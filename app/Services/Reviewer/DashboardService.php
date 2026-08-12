<?php

namespace App\Services\Reviewer;

use Illuminate\Support\Facades\Auth;

class DashboardService
{
    /**
     * Get dashboard statistics for the currently logged-in reviewer.
     * Currently returns dummy data until the review tables are implemented.
     */
    public function getStats(): array
    {
        $user = Auth::user();

        // TODO: Replace with real database queries once ReviewAssignment models are created.
        return [
            'total_assigned' => 0,
            'pending_reviews' => 0,
            'completed_reviews' => 0,
            'upcoming_deadlines' => 0,
        ];
    }
}
