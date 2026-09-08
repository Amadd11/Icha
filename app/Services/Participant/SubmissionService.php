<?php

namespace App\Services\Participant;

use App\Exceptions\SubmissionException;
use App\Helpers\CodeGenerator;
use App\Models\AbstractSubmission;
use App\Models\Category;
use App\Models\Conference;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SubmissionService
{
    /**
     * Get all necessary data for the participant submission page.
     */
    public function getSubmissionData(User $user): array
    {
        $activeConference = $this->getActiveConference();

        $categories = $activeConference
            ? Category::where('conference_id', $activeConference->id)->orWhereNull('conference_id')->get(['id', 'name', 'badge'])
            : Category::all(['id', 'name', 'badge']);

        $abstracts = AbstractSubmission::with(['category', 'reviewRounds.assignments.review'])
            ->where('user_id', $user->id)
            ->when($activeConference, fn($q) => $q->where('conference_id', $activeConference->id))
            ->latest()
            ->get();

        // Check registration and verified payment
        $registration = Registration::with('registrationFee')
            ->where('user_id', $user->id)
            ->when($activeConference, fn($q) => $q->where('conference_id', $activeConference->id))
            ->latest()
            ->first();

        $isPaid = Payment::where('registration_id', $registration?->id)
            ->where('status', 'verified')
            ->exists();

        // Determine if ticket type is Presenter or Non-Presenter
        $isPresenter = $registration?->registrationFee ? ($registration->registrationFee->type === 'presenter') : true;

        $hasUploadedAbstract = $abstracts->count() > 0;
        $isAbstractAccepted = $abstracts->where('status', 'accepted')->isNotEmpty();
        $isAbstractRevisionRequired = $abstracts->where('status', 'revision_required')->isNotEmpty();
        $userCode = 'ICHA-' . str_pad($user->id, 4, '0', STR_PAD_LEFT);

        $formattedAbstracts = $abstracts->map(function ($abstract) {
            $feedbacks = $abstract->reviewRounds->flatMap(function ($round) {
                return $round->assignments->map(function ($assignment, $idx) use ($round) {
                    $review = $assignment->review;
                    if (!$review) return null;
                    return [
                        'round_number'      => $round->round_number,
                        'reviewer_alias'    => 'Reviewer #' . ($idx + 1),
                        'recommendation'    => $review->recommendation,
                        'score_criteria_1'  => $review->score_criteria_1,
                        'score_criteria_2'  => $review->score_criteria_2,
                        'comments'          => $review->summary,
                        'reviewed_at'       => $review->created_at?->format('d M Y, H:i'),
                    ];
                })->filter();
            })->values();

            $item = $abstract->toArray();
            $item['reviewer_feedbacks'] = $feedbacks;
            return $item;
        });

        return [
            'activeConference' => $activeConference,
            'categories'       => $categories,
            'abstracts'        => $formattedAbstracts,
            'isPaid'           => $isPaid,
            'isPresenter'      => $isPresenter,
            'registration'     => $registration,
            'statusChecklist'  => [
                'hasUploadedAbstract'        => $hasUploadedAbstract,
                'isAbstractAccepted'         => $isAbstractAccepted,
                'isAbstractRevisionRequired' => $isAbstractRevisionRequired,
                'hasPaid'                    => $isPaid,
                'zoomLink'                   => 'Coming Soon.',
            ],
            'userSummary'      => [
                'name'         => $user->name,
                'role'         => ucfirst($user->role),
                'code'         => $userCode,
                'package_name' => $registration?->registrationFee?->name ?? 'Regular Participant',
                'ticket_type'  => $registration?->registrationFee?->type ?? 'presenter',
            ],
        ];
    }

    /**
     * Handle Abstract Submission logic & file storage.
     */
    public function submitAbstract(User $user, array $data, ?UploadedFile $file = null): AbstractSubmission
    {
        $activeConference = $this->getActiveConference();

        // 1. Verify Payment
        $registration = Registration::with('registrationFee')
            ->where('user_id', $user->id)
            ->when($activeConference, fn($q) => $q->where('conference_id', $activeConference->id))
            ->latest()
            ->first();

        $isPaid = $registration && Payment::where('registration_id', $registration->id)->where('status', 'verified')->exists();

        if (!$isPaid) {
            throw new SubmissionException('Payment verification is required before submitting an abstract.', 403);
        }

        // 2. Verify Presenter Ticket Type (Business Rule Section 2)
        if ($registration->registrationFee && $registration->registrationFee->type === 'non_presenter') {
            throw new SubmissionException('Peserta dengan tiket Non-Presenter tidak memiliki akses pengunggahan abstrak.', 403);
        }

        // 3. Verify Category exists and belongs to active conference
        $category = Category::findOrFail($data['category_id']);
        if ($activeConference && $category->conference_id && (int) $category->conference_id !== (int) $activeConference->id) {
            throw new SubmissionException('Kategori ilmiah tidak sesuai dengan konferensi yang sedang aktif.', 422);
        }

        // 4. Block early if abstract submission is closed or deadline passed.
        if ($activeConference && !$activeConference->isAbstractSubmissionOpen()) {
            throw new SubmissionException('Penerimaan abstrak saat ini sedang ditutup atau batas waktu (deadline) telah berakhir.', 403);
        }

        // 5. Block early if an existing abstract is already locked-in as accepted.
        //    Checked *before* storing the file so we don't leave orphaned uploads.
        $existingAbstractPeek = AbstractSubmission::where('user_id', $user->id)
            ->when($activeConference, fn($q) => $q->where('conference_id', $activeConference->id))
            ->latest()
            ->first();

        if ($existingAbstractPeek && $existingAbstractPeek->status === 'accepted') {
            throw new SubmissionException('Abstrak Anda telah dinyatakan Diterima (Accepted) dan naskah telah terkunci untuk prosiding.', 403);
        }

        $filePath = null;
        if ($file) {
            $filePath = $file->store('abstracts', 'public');
        }

        try {
            // Atomic lock per user prevents double-submit race condition
            return Cache::lock("submit_abstract_user_{$user->id}", 10)->block(5, function () use ($user, $data, $filePath, $activeConference) {
                return DB::transaction(function () use ($user, $data, $filePath, $activeConference) {
                    // Check if there is an existing abstract (e.g. revision required) with lockForUpdate
                    $existingAbstract = AbstractSubmission::where('user_id', $user->id)
                        ->when($activeConference, fn($q) => $q->where('conference_id', $activeConference->id))
                        ->lockForUpdate()
                        ->latest()
                        ->first();

                    if ($existingAbstract) {
                        if ($existingAbstract->status === 'accepted') {
                            throw new SubmissionException('Abstrak Anda telah dinyatakan Diterima (Accepted) dan naskah telah terkunci untuk prosiding.', 403);
                        }

                        $isRevision = ($existingAbstract->status === 'revision_required');

                        // Delete previous file when a new revision file is uploaded
                        if ($filePath && $existingAbstract->file_path && $existingAbstract->file_path !== $filePath) {
                            Storage::disk('public')->delete($existingAbstract->file_path);
                        }

                        // Update existing abstract for Resubmission / Replacement
                        $existingAbstract->update([
                            'title'             => $data['title'],
                            'category_id'       => $data['category_id'],
                            'abstract_text'     => $data['abstract_text'] ?? $existingAbstract->abstract_text,
                            'keywords'          => $data['keywords'] ?? $existingAbstract->keywords,
                            'presentation_type' => $data['presentation_type'] ?? $existingAbstract->presentation_type,
                            'file_path'         => $filePath ?? $existingAbstract->file_path,
                            'status'            => 'under_review',
                        ]);

                        if ($isRevision) {
                            $this->createRevisionRound(
                                submissionType: 'abstract',
                                submissionId: $existingAbstract->id,
                                fallbackCategoryId: $data['category_id']
                            );
                        }

                        return $existingAbstract;
                    }

                    // Database-agnostic & collision-free sequential code generation (ABS-001, ABS-002, ...)
                    // Atomic creation inside lock eliminates TOCTOU race conditions.
                    return CodeGenerator::create(AbstractSubmission::class, 'abstract_code', 'ABS', [
                        'user_id'           => $user->id,
                        'conference_id'     => $activeConference?->id,
                        'category_id'       => $data['category_id'],
                        'title'             => $data['title'],
                        'abstract_text'     => $data['abstract_text'] ?? null,
                        'keywords'          => $data['keywords'] ?? null,
                        'presentation_type' => $data['presentation_type'] ?? 'oral',
                        'file_path'         => $filePath,
                        'status'            => 'pending',
                    ]);
                });
            });
        } catch (\Throwable $e) {
            // Roll back the uploaded file if anything after storage failed,
            // so we don't leave orphaned files in storage.
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            throw $e;
        }
    }

    /**
     * Resolve the currently active conference, falling back to the most recent one.
     */
    private function getActiveConference(): ?Conference
    {
        return Conference::where('is_active', true)->first() ?? Conference::latest()->first();
    }

    /**
     * Create a new review round for a resubmitted (revision_required) submission,
     * assigning it to the reviewers who requested the revision, falling back
     * to previous-round reviewers, then to any reviewer in the same category.
     */
    private function createRevisionRound(string $submissionType, int $submissionId, int $fallbackCategoryId): ReviewRound
    {
        $latestRound = ReviewRound::where('submission_type', $submissionType)
            ->where('submission_id', $submissionId)
            ->with(['assignments.review'])
            ->orderByDesc('id')
            ->first();

        $reviewerIds = $this->resolveRevisingReviewers($latestRound, $fallbackCategoryId);

        if (empty($reviewerIds)) {
            throw new SubmissionException('Tidak ada reviewer yang tersedia untuk mengevaluasi revisi.', 422);
        }

        $newRound = ReviewRound::create([
            'submission_type' => $submissionType,
            'submission_id'   => $submissionId,
            'round_number'    => ($latestRound?->round_number ?? 1) + 1,
            'status'          => 'open',
        ]);

        foreach ($reviewerIds as $reviewerId) {
            ReviewAssignment::create([
                'review_round_id' => $newRound->id,
                'reviewer_id'     => $reviewerId,
                'status'          => 'assigned',
            ]);
        }

        return $newRound;
    }

    /**
     * Determine which reviewers should be assigned to a new revision round:
     * - Only reviewer(s) from previous round who explicitly recommended REVISION / REVISION_REQUIRED
     * - Fallback to reviewers who did not explicitly accept if none specifically chose revision
     * - Fallback to all reviewers from the previous round if all accepted (e.g. editorial revision decision)
     * - Fallback to up to 3 reviewers from the category if there was no previous round
     */
    private function resolveRevisingReviewers(?ReviewRound $latestRound, int $fallbackCategoryId): array
    {
        $revisingReviewerIds = [];

        if ($latestRound && $latestRound->assignments->isNotEmpty()) {
            // 1. Primary: Only reviewers who explicitly recommended REVISION / REVISION_REQUIRED
            foreach ($latestRound->assignments as $assignment) {
                $recommendation = strtoupper(trim((string) ($assignment->review?->recommendation ?? '')));
                if (in_array($recommendation, ['REVISION', 'REVISION_REQUIRED'], true)) {
                    $revisingReviewerIds[] = $assignment->reviewer_id;
                }
            }

            // 2. Fallback: If no reviewer specifically recommended revision, include reviewers who did NOT accept
            if (empty($revisingReviewerIds)) {
                foreach ($latestRound->assignments as $assignment) {
                    $recommendation = strtoupper(trim((string) ($assignment->review?->recommendation ?? '')));
                    $alreadyAccepted = in_array($recommendation, ['ORAL', 'POSTER', 'ACCEPT', 'ACCEPTED'], true);
                    if (!$alreadyAccepted) {
                        $revisingReviewerIds[] = $assignment->reviewer_id;
                    }
                }
            }

            // 3. Fallback: If still empty (e.g. all reviewers said accepted, but admin decided revision), re-assign all previous reviewers
            if (empty($revisingReviewerIds)) {
                $revisingReviewerIds = $latestRound->assignments->pluck('reviewer_id')->toArray();
            }

            $revisingReviewerIds = array_values(array_unique($revisingReviewerIds));
        }

        // 4. Fallback if there was no previous round or assignments
        if (empty($revisingReviewerIds)) {
            $revisingReviewerIds = User::where('role', 'reviewer')
                ->whereHas('categories', fn($q) => $q->where('categories.id', $fallbackCategoryId))
                ->take(3)
                ->pluck('id')
                ->toArray();
        }

        return $revisingReviewerIds;
    }
}
