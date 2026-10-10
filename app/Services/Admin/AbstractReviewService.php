<?php

namespace App\Services\Admin;

use App\Models\AbstractSubmission;
use App\Models\Conference;
use App\Models\ReviewRound;
use App\Models\User;
use App\Services\Admin\ReviewAssignmentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class AbstractReviewService
{
    public function __construct(
        protected ReviewAssignmentService $assignmentService
    ) {}

    /**
     * Get abstract submissions filtered by status with pagination.
     */
    public function getAbstracts(?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        // Auto-assign any pending abstracts to category reviewers
        $pendingAbstracts = AbstractSubmission::where('status', 'pending')
            ->whereNotNull('category_id')
            ->get();
        foreach ($pendingAbstracts as $abs) {
            $this->assignmentService->autoAssignCategoryReviewers($abs);
        }

        // Auto-decide any submissions that reached required completed reviews
        $undecidedRounds = ReviewRound::where('submission_type', 'abstract')
            ->whereIn('status', ['open', 'locked'])
            ->get();
        foreach ($undecidedRounds as $round) {
            $round->applyAutoDecision();
        }

        $confId = request()->query('conference_id') ?? session('admin_conference_id') ?? Conference::where('is_active', true)->first()?->id;

        $query = AbstractSubmission::with([
            'user.profile',
            'category',
            'conference',
            'reviewRounds.assignments.reviewer',
            'reviewRounds.assignments.review',
        ])
            ->when($confId, fn($q) => $q->where('conference_id', $confId))
            ->latest();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Process review decision for an abstract.
     */
    public function reviewAbstract(AbstractSubmission $abstract, User $reviewer, array $data): bool
    {
        $round = $this->assertFinalDecisionAllowed($abstract, $data['status'], $data['presentation_type'] ?? null);

        $updateData = [
            'status'       => $data['status'],
            'review_notes' => $data['review_notes'] ?? null,
            'reviewed_by'  => $reviewer->id,
            'reviewed_at'  => now(),
        ];

        if ($data['status'] === 'accepted') {
            $presentationType = !empty($data['presentation_type'])
                ? strtolower($data['presentation_type'])
                : ($round ? strtolower((string) $round->recommendedPresentationType()) : 'oral');
            $updateData['presentation_type'] = in_array($presentationType, ['oral', 'poster'], true) ? $presentationType : 'oral';
        }

        $updated = $abstract->update($updateData);

        if ($round && in_array($round->status, ['locked', 'open'], true)) {
            $round->update(['status' => 'completed']);
        }

        return $updated;
    }

    private function assertFinalDecisionAllowed(AbstractSubmission $abstract, string $status, ?string $presentationType): ?ReviewRound
    {
        if (!in_array($status, ['accepted', 'revision_required', 'rejected'], true)) {
            return null;
        }

        $round = ReviewRound::where('submission_type', 'abstract')
            ->where('submission_id', $abstract->id)
            ->latest('round_number')
            ->first();

        if (!$round) {
            throw ValidationException::withMessages([
                'status' => 'Tidak ditemukan tahap penilaian (review round) untuk naskah ini.',
            ]);
        }

        $totalAssignments = $round->assignments()->count();
        $completedAssignments = $round->assignments()->where('status', 'completed')->count();

        // Admin has full right to override / change an already decided submission
        $isModifyingExistingDecision = in_array($abstract->status, ['accepted', 'revision_required', 'rejected'], true);

        // Require 3 reviewers to complete review before final decision (unless desk rejected by admin or modifying existing decision)
        if (!$isModifyingExistingDecision && $status !== 'rejected') {
            if ($round->round_number === 1) {
                $expectedReviews = min(3, max(1, $totalAssignments));
                if ($completedAssignments < $expectedReviews) {
                    throw ValidationException::withMessages([
                        'status' => "Final decision memerlukan 3 reviewer menyelesaikan telaah (saat ini baru {$completedAssignments}/{$expectedReviews} reviewer yang selesai).",
                    ]);
                }
            } else {
                if ($totalAssignments === 0 || $completedAssignments < $totalAssignments) {
                    throw ValidationException::withMessages([
                        'status' => "Final decision memerlukan seluruh reviewer pada tahap revisi ini menyelesaikan telaah (saat ini: {$completedAssignments}/{$totalAssignments}).",
                    ]);
                }
            }
        }

        // Auto-lock if all reviews are completed and round is still open
        if ($round->status === 'open' && $completedAssignments >= ($round->round_number === 1 ? 3 : $totalAssignments)) {
            $round->update(['status' => 'locked']);
        }

        return $round;
    }
}
