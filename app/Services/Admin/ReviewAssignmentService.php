<?php

namespace App\Services\Admin;

use App\Models\AbstractSubmission;
use App\Models\ReviewRound;
use App\Models\ReviewAssignment;
use Illuminate\Support\Facades\DB;

class ReviewAssignmentService
{
    public function assignReviewers(AbstractSubmission $abstract, array $reviewerIds): void
    {
        // Enforce max 3 reviewers per round specification
        $reviewerIds = array_values(array_unique(array_slice($reviewerIds, 0, 3)));

        DB::transaction(function () use ($abstract, $reviewerIds) {
            // Find or create the latest review round for this abstract
            $round = ReviewRound::firstOrCreate(
                [
                    'submission_type' => 'abstract',
                    'submission_id'   => $abstract->id,
                    'status'          => 'pending',
                ]
            );

            // Get existing assignments for this round
            $existingAssignments = $round->assignments()->pluck('reviewer_id')->toArray();

            // Reviewers to add
            $toAdd = array_diff($reviewerIds, $existingAssignments);
            // Reviewers to remove
            $toRemove = array_diff($existingAssignments, $reviewerIds);

            if (!empty($toRemove)) {
                $round->assignments()->whereIn('reviewer_id', $toRemove)->delete();
            }

            foreach ($toAdd as $reviewerId) {
                ReviewAssignment::create([
                    'review_round_id' => $round->id,
                    'reviewer_id' => $reviewerId,
                    'status' => 'assigned',
                ]);
            }
        });
    }
}
