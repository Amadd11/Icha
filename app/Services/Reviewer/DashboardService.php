<?php

namespace App\Services\Reviewer;

use App\Models\ReviewAssignment;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
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
