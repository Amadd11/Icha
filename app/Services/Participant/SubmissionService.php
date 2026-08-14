<?php

namespace App\Services\Participant;

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

class SubmissionService
{
    /**
     * Get all necessary data for the participant submission page.
     */
    public function getSubmissionData(User $user): array
    {
        $activeConference = Conference::where('is_active', true)->first() ?? Conference::latest()->first();

        $categories = $activeConference 
            ? Category::where('conference_id', $activeConference->id)->orWhereNull('conference_id')->get(['id', 'name', 'badge'])
            : Category::all(['id', 'name', 'badge']);

        $abstracts = AbstractSubmission::with(['category', 'reviewRounds.assignments.review'])
            ->where('user_id', $user->id)
            ->when($activeConference, fn($q) => $q->where('conference_id', $activeConference->id))
            ->latest()
            ->get();

        $papers = FullPaper::with('abstract')
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
            ]
        ];
    }

    /**
     * Handle Abstract Submission logic & file storage.
     */
    public function submitAbstract(User $user, array $data, ?UploadedFile $file = null): AbstractSubmission
    {
        $activeConference = Conference::where('is_active', true)->first() ?? Conference::latest()->first();

        // 1. Verify Payment
        $registration = Registration::with('registrationFee')
            ->where('user_id', $user->id)
            ->when($activeConference, fn($q) => $q->where('conference_id', $activeConference->id))
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

        $filePath = null;
        if ($file) {
            $filePath = $file->store('abstracts', 'public');
        }

        // Check if there is an existing abstract (e.g. revision required)
        $existingAbstract = AbstractSubmission::where('user_id', $user->id)
            ->when($activeConference, fn($q) => $q->where('conference_id', $activeConference->id))
            ->latest()
            ->first();

        if ($existingAbstract && $existingAbstract->status === 'revision_required') {
            // Update existing abstract for Resubmission
            $existingAbstract->update([
                'title'             => $data['title'],
                'category_id'       => $data['category_id'],
                'abstract_text'     => $data['abstract_text'] ?? $existingAbstract->abstract_text,
                'keywords'          => $data['keywords'] ?? $existingAbstract->keywords,
                'presentation_type' => $data['presentation_type'] ?? $existingAbstract->presentation_type,
                'file_path'         => $filePath ?? $existingAbstract->file_path,
                'status'            => 'under_review',
            ]);

            // Create New Review Round (Round 2)
            $latestRound = ReviewRound::where('submission_type', 'abstract')
                ->where('submission_id', $existingAbstract->id)
                ->orderByDesc('id')
                ->first();

            $newRoundNumber = ($latestRound?->round_number ?? 1) + 1;

            $newRound = ReviewRound::create([
                'submission_type' => 'abstract',
                'submission_id'   => $existingAbstract->id,
                'round_number'    => $newRoundNumber,
                'status'          => 'pending',
            ]);

            // Re-assign reviewers for the new round
            $matchingReviewers = User::where('role', 'reviewer')
                ->whereHas('categories', function ($q) use ($data) {
                    $q->where('categories.id', $data['category_id']);
                })
                ->take(3)
                ->get();

            foreach ($matchingReviewers as $rev) {
                ReviewAssignment::create([
                    'review_round_id' => $newRound->id,
                    'reviewer_id'     => $rev->id,
                    'status'          => 'assigned',
                ]);
            }

            return $existingAbstract;
        }

        // Create New Abstract Submission
        $abstractCount = AbstractSubmission::count() + 1;
        $abstractCode = 'ABS-' . date('Y') . '-' . str_pad($abstractCount, 4, '0', STR_PAD_LEFT);

        $abstract = AbstractSubmission::create([
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

        // Auto-assign to reviewers matching category (Max 3 Reviewers)
        $matchingReviewers = User::where('role', 'reviewer')
            ->whereHas('categories', function ($q) use ($data) {
                $q->where('categories.id', $data['category_id']);
            })
            ->take(3)
            ->get();

        if ($matchingReviewers->isNotEmpty()) {
            $round = ReviewRound::firstOrCreate([
                'submission_type' => 'abstract',
                'submission_id'   => $abstract->id,
            ], [
                'status' => 'pending',
            ]);

            foreach ($matchingReviewers as $rev) {
                ReviewAssignment::firstOrCreate([
                    'review_round_id' => $round->id,
                    'reviewer_id'     => $rev->id,
                ], [
                    'status' => 'assigned',
                ]);
            }
        }

        return $abstract;
    }

    /**
     * Handle Full Paper Submission logic & file storage.
     */
    public function submitPaper(User $user, array $data, ?UploadedFile $file = null): FullPaper
    {
        $activeConference = Conference::where('is_active', true)->first() ?? Conference::latest()->first();

        // 1. Verify Payment
        $registration = Registration::with('registrationFee')
            ->where('user_id', $user->id)
            ->when($activeConference, fn($q) => $q->where('conference_id', $activeConference->id))
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

        $paperCount = FullPaper::count() + 1;
        $paperCode = 'FP-' . date('Y') . '-' . str_pad($paperCount, 4, '0', STR_PAD_LEFT);

        return FullPaper::create([
            'user_id'       => $user->id,
            'conference_id' => $activeConference?->id,
            'abstract_id'   => $abstract->id,
            'paper_code'    => $paperCode,
            'title'         => $data['title'],
            'file_path'     => $filePath,
            'status'        => 'pending',
        ]);
    }
}
