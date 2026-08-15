<?php

namespace App\Services\Reviewer;

use App\Models\ReviewAssignment;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

class ReviewSubmissionService
{
    public function submitReview(ReviewAssignment $assignment, array $data): Review
    {
        return DB::transaction(function () use ($assignment, $data) {
            $totalScore = $data['score_criteria_1'] + $data['score_criteria_2'];

            $review = Review::updateOrCreate(
                ['review_assignment_id' => $assignment->id],
                [
                    'score_criteria_1' => $data['score_criteria_1'],
                    'score_criteria_2' => $data['score_criteria_2'],
                    'total_score' => $totalScore,
                    'recommendation' => $data['recommendation'],
                    'summary' => $data['summary'] ?? null,
                ]
            );

            $assignment->update(['status' => 'completed']);

            $round = $assignment->round;

            // Update submission status to under_review if still pending
            if ($round && $round->submission_type === 'abstract' && $round->abstractSubmission) {
                if ($round->abstractSubmission->status === 'pending') {
                    $round->abstractSubmission->update(['status' => 'under_review']);
                }
            }

            // Locking Logic: If all assignments in round are completed, lock round
            $totalAssignments = $round->assignments()->count();
            $completedAssignments = $round->assignments()->where('status', 'completed')->count();

            if ($totalAssignments > 0 && $totalAssignments === $completedAssignments) {
                $round->update(['status' => 'locked']);
            }

            return $review;
        });
    }
}
