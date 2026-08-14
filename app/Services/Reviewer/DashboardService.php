<?php

namespace App\Services\Reviewer;

use App\Models\AbstractSubmission;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    /**
     * Synchronize and auto-assign abstracts matching the reviewer's categories.
     */
    private function syncTrackAssignments(User $user): void
    {
        $categoryIds = $user->categories()->pluck('categories.id')->toArray();
        if (empty($categoryIds)) {
            return;
        }

        $abstracts = AbstractSubmission::whereIn('category_id', $categoryIds)->get();

        foreach ($abstracts as $abstract) {
            $round = ReviewRound::firstOrCreate(
                [
                    'submission_type' => 'abstract',
                    'submission_id'   => $abstract->id,
                ],
                [
                    'status' => 'pending',
                ]
            );

            // Ensure max 3 reviewers per round
            $isAssigned = $round->assignments()->where('reviewer_id', $user->id)->exists();
            if (!$isAssigned && $round->assignments()->count() < 3) {
                ReviewAssignment::create([
                    'review_round_id' => $round->id,
                    'reviewer_id'     => $user->id,
                    'status'          => 'assigned',
                ]);
            }
        }
    }

    /**
     * Get dashboard statistics for the currently logged-in reviewer.
     */
    public function getStats(): array
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return [
                'total_assigned'     => 0,
                'pending_reviews'    => 0,
                'completed_reviews'  => 0,
                'upcoming_deadlines' => 0,
            ];
        }

        $this->syncTrackAssignments($user);

        $assignments = ReviewAssignment::where('reviewer_id', $user->id)->get();

        return [
            'total_assigned'     => $assignments->count(),
            'pending_reviews'    => $assignments->where('status', 'assigned')->count(),
            'completed_reviews'  => $assignments->where('status', 'completed')->count(),
            'upcoming_deadlines' => 0,
        ];
    }

    /**
     * Get all assignments for the currently logged-in reviewer with relationships.
     */
    public function getAssignments()
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return collect();
        }

        $this->syncTrackAssignments($user);

        return ReviewAssignment::with([
            'round.abstractSubmission.category',
            'round.fullPaper',
            'review'
        ])
        ->where('reviewer_id', $user->id)
        ->latest('id')
        ->get();
    }
}
