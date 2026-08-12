<?php

namespace App\Services\Reviewer;

use Illuminate\Support\Facades\Auth;

class DashboardService
{
    /**
     * Get dashboard statistics for the currently logged-in reviewer.
     */
    public function getStats(): array
    {
        $user = Auth::user();
        $assignments = $user->reviewerAssignments ?? \App\Models\ReviewAssignment::where('reviewer_id', $user->id)->get();

        return [
            'total_assigned' => $assignments->count(),
            'pending_reviews' => $assignments->where('status', 'assigned')->count(),
            'completed_reviews' => $assignments->where('status', 'completed')->count(),
            'upcoming_deadlines' => 0, // Placeholder for Phase 8 timeline logic
        ];
    }

    /**
     * Get all assignments for the currently logged-in reviewer with relationships.
     */
    public function getAssignments()
    {
        $user = Auth::user();

        return \App\Models\ReviewAssignment::with([
            'round.abstractSubmission.category',
            'round.fullPaper',
            'review'
        ])
        ->where('reviewer_id', $user->id)
        ->latest()
        ->get();
    }
}
