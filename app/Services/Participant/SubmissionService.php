<?php

namespace App\Services\Participant;

use App\Helpers\CodeGenerator;
use App\Models\AbstractSubmission;
use App\Models\Category;
use App\Models\Conference;
use App\Models\FullPaper;
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
            ->when($activeConference, fn ($q) => $q->where('conference_id', $activeConference->id))
            ->latest()
            ->get();

        $papers = FullPaper::with('abstract')
            ->where('user_id', $user->id)
            ->when($activeConference, fn ($q) => $q->where('conference_id', $activeConference->id))
            ->latest()
            ->get();

        // Check registration and verified payment
        $registration = Registration::with('registrationFee')
            ->where('user_id', $user->id)
            ->when($activeConference, fn ($q) => $q->where('conference_id', $activeConference->id))
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

        return [
            'activeConference' => $activeConference,
            'categories'       => $categories,
            'abstracts'        => $abstracts,
            'papers'           => $papers,
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
            ->when($activeConference, fn ($q) => $q->where('conference_id', $activeConference->id))
            ->latest()
            ->first();

        $isPaid = $registration && Payment::where('registration_id', $registration->id)->where('status', 'verified')->exists();

        if (!$isPaid) {
            abort(403, 'Payment verification is required before submitting an abstract.');
        }

        // 2. Verify Presenter Ticket Type (Business Rule Section 2)
        if ($registration->registrationFee && $registration->registrationFee->type === 'non_presenter') {
            abort(403, 'Peserta dengan tiket Non-Presenter tidak memiliki akses pengunggahan abstrak.');
        }

        // 3. Block early if an existing abstract is already locked-in as accepted.
        //    Checked *before* storing the file so we don't leave orphaned uploads.
        $existingAbstractPeek = AbstractSubmission::where('user_id', $user->id)
            ->when($activeConference, fn ($q) => $q->where('conference_id', $activeConference->id))
            ->latest()
            ->first();

        if ($existingAbstractPeek && $existingAbstractPeek->status === 'accepted') {
            abort(403, 'Abstrak Anda telah dinyatakan Diterima (Accepted) dan naskah telah terkunci untuk prosiding.');
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
                        ->when($activeConference, fn ($q) => $q->where('conference_id', $activeConference->id))
                        ->lockForUpdate()
                        ->latest()
                        ->first();

                    if ($existingAbstract) {
                        if ($existingAbstract->status === 'accepted') {
                            abort(403, 'Abstrak Anda telah dinyatakan Diterima (Accepted) dan naskah telah terkunci untuk prosiding.');
                        }

                        $isRevision = ($existingAbstract->status === 'revision_required');

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
                    $abstractCode = CodeGenerator::next(AbstractSubmission::class, 'abstract_code', 'ABS');

                    return AbstractSubmission::create([
                        'user_id'           => $user->id,
                        'conference_id'     => $activeConference?->id,
                        'category_id'       => $data['category_id'],
                        'abstract_code'     => $abstractCode,
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
     * Handle Full Paper Submission logic & file storage.
     */
    public function submitPaper(User $user, array $data, ?UploadedFile $file = null): FullPaper
    {
        $activeConference = $this->getActiveConference();

        // 1. Verify Payment
        $registration = Registration::with('registrationFee')
            ->where('user_id', $user->id)
            ->when($activeConference, fn ($q) => $q->where('conference_id', $activeConference->id))
            ->latest()
            ->first();

        $isPaid = $registration && Payment::where('registration_id', $registration->id)->where('status', 'verified')->exists();

        if (!$isPaid) {
            abort(403, 'Payment verification is required before submitting a full paper.');
        }

        // 2. Verify Presenter Ticket Type
        if ($registration->registrationFee && $registration->registrationFee->type === 'non_presenter') {
            abort(403, 'Peserta dengan tiket Non-Presenter tidak memiliki akses pengunggahan full paper.');
        }

        // 3. Verify Abstract is Accepted (Section 15 & 16)
        $abstract = AbstractSubmission::find($data['abstract_id'] ?? null);
        if (!$abstract || $abstract->status !== 'accepted') {
            abort(403, 'Full Paper hanya dapat diunggah setelah Abstrak dinyatakan Diterima (Accepted).');
        }

        $filePath = null;
        if ($file) {
            $filePath = $file->store('papers', 'public');
        }

        try {
            // Atomic lock per abstract prevents double-submit race condition
            return Cache::lock("submit_paper_abstract_{$abstract->id}", 10)->block(5, function () use ($user, $data, $filePath, $activeConference, $abstract) {
                return DB::transaction(function () use ($user, $data, $filePath, $activeConference, $abstract) {
                    // Check for existing paper for this abstract (e.g. revision / update) with lockForUpdate
                    $existingPaper = FullPaper::where('abstract_id', $abstract->id)->lockForUpdate()->first();

                    if ($existingPaper) {
                        $isRevision = ($existingPaper->status === 'revision_required');

                        $existingPaper->update([
                            'title'     => $data['title'],
                            'file_path' => $filePath ?? $existingPaper->file_path,
                            'status'    => 'under_review',
                        ]);

                        if ($isRevision) {
                            $this->createRevisionRound(
                                submissionType: 'full_paper',
                                submissionId: $existingPaper->id,
                                fallbackCategoryId: $abstract->category_id
                            );
                        }

                        return $existingPaper;
                    }

                    // Database-agnostic & collision-free sequential code generation (FP-001, FP-002, ...)
                    $paperCode = CodeGenerator::next(FullPaper::class, 'paper_code', 'FP');

                    return FullPaper::create([
                        'user_id'       => $user->id,
                        'conference_id' => $activeConference?->id,
                        'abstract_id'   => $abstract->id,
                        'paper_code'    => $paperCode,
                        'title'         => $data['title'],
                        'file_path'     => $filePath,
                        'status'        => 'under_review',
                    ]);
                });
            });
        } catch (\Throwable $e) {
            // Roll back the uploaded file if anything after storage failed
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

        $newRound = ReviewRound::create([
            'submission_type' => $submissionType,
            'submission_id'   => $submissionId,
            'round_number'    => ($latestRound?->round_number ?? 1) + 1,
            'status'          => 'pending',
        ]);

        foreach (array_unique($this->resolveRevisingReviewers($latestRound, $fallbackCategoryId)) as $reviewerId) {
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
     * - Reviewers from the previous round who did NOT already accept
     *   (i.e. still need to review the revised submission)
     * - Fallback to all reviewers from the previous round if none qualify
     * - Fallback to up to 3 reviewers from the same category if there was no previous round
     */
    private function resolveRevisingReviewers(?ReviewRound $latestRound, int $fallbackCategoryId): array
    {
        $revisingReviewerIds = [];

        if ($latestRound && $latestRound->assignments->isNotEmpty()) {
            foreach ($latestRound->assignments as $assignment) {
                $recommendation = strtolower($assignment->review?->recommendation ?? '');
                $alreadyAccepted = in_array($recommendation, ['oral', 'poster', 'accepted']);
                if (!$alreadyAccepted) {
                    $revisingReviewerIds[] = $assignment->reviewer_id;
                }
            }

            if (empty($revisingReviewerIds)) {
                $revisingReviewerIds = $latestRound->assignments->pluck('reviewer_id')->toArray();
            }
        }

        if (empty($revisingReviewerIds)) {
            $revisingReviewerIds = User::where('role', 'reviewer')
                ->whereHas('categories', fn ($q) => $q->where('categories.id', $fallbackCategoryId))
                ->take(3)
                ->pluck('id')
                ->toArray();
        }

        return $revisingReviewerIds;
    }
}
