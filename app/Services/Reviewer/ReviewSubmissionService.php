<?php

namespace App\Services\Reviewer;

use App\Models\ReviewAssignment;
use App\Models\Review;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReviewSubmissionService
{
    public function submitReview(ReviewAssignment $assignment, array $data): Review
    {
        return Cache::lock("submit_review_assignment_{$assignment->id}", 10)->block(5, function () use ($assignment, $data) {
            return DB::transaction(function () use ($assignment, $data) {
                $totalScore = $data['score_criteria_1'] + $data['score_criteria_2'];

                // Check existing review with lockForUpdate to guarantee uniqueness
                $review = Review::where('review_assignment_id', $assignment->id)->lockForUpdate()->first();

                if ($review) {
                    $review->update([
                        'score_criteria_1' => $data['score_criteria_1'],
                        'score_criteria_2' => $data['score_criteria_2'],
                        'total_score'      => $totalScore,
                        'recommendation'   => $data['recommendation'],
                        'summary'          => $data['summary'] ?? null,
                    ]);
                } else {
                    $review = Review::create([
                        'review_assignment_id' => $assignment->id,
                        'score_criteria_1'     => $data['score_criteria_1'],
                        'score_criteria_2'     => $data['score_criteria_2'],
                        'total_score'          => $totalScore,
                        'recommendation'       => $data['recommendation'],
                        'summary'              => $data['summary'] ?? null,
                    ]);
                }

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
        });
    }
}
