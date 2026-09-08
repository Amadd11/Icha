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
                $assignment->loadMissing(['round.abstractSubmission', 'round.fullPaper']);
                $round = $assignment->round;
                $submission = $round?->abstractSubmission ?? $round?->fullPaper;

                $isDecided = !$round || $round->status === 'completed'
                    || in_array($submission?->status, ['accepted', 'rejected'], true)
                    || ($submission?->status === 'revision_required' && $round->status !== 'open');

                if ($isDecided || !in_array($assignment->status, ['assigned', 'completed'], true)) {
                    throw new \DomainException('Review assignment is not available for submission.');
                }

                if ($round->status === 'pending') {
                    $round->update(['status' => 'open']);
                }

                $totalScore = (int) $data['score_criteria_1'] + (int) $data['score_criteria_2'];

                $rawRec = strtoupper((string) ($data['recommendation'] ?? ''));
                $isFullPaper = ($round->submission_type === 'full_paper');

                $recommendation = match ($rawRec) {
                    'ACCEPTED', 'ACCEPT' => 'ACCEPTED',
                    'ORAL', 'ACCEPT_ORAL' => $isFullPaper ? 'ACCEPTED' : 'ORAL',
                    'POSTER', 'ACCEPT_POSTER' => $isFullPaper ? 'ACCEPTED' : 'POSTER',
                    'REVISION', 'REVISION_REQUIRED' => 'REVISION',
                    'REJECT', 'REJECTED' => 'REJECT',
                    default => ($totalScore >= 5 ? ($isFullPaper ? 'ACCEPTED' : 'ORAL') : ($isFullPaper ? 'REJECT' : 'POSTER')),
                };

                // Check existing review with lockForUpdate to guarantee uniqueness
                $review = Review::where('review_assignment_id', $assignment->id)->lockForUpdate()->first();

                if ($review) {
                    $review->update([
                        'score_criteria_1' => $data['score_criteria_1'],
                        'score_criteria_2' => $data['score_criteria_2'],
                        'total_score'      => $totalScore,
                        'recommendation'   => $recommendation,
                        'summary'          => $data['summary'] ?? null,
                    ]);
                } else {
                    $review = Review::create([
                        'review_assignment_id' => $assignment->id,
                        'score_criteria_1'     => $data['score_criteria_1'],
                        'score_criteria_2'     => $data['score_criteria_2'],
                        'total_score'          => $totalScore,
                        'recommendation'       => $recommendation,
                        'summary'              => $data['summary'] ?? null,
                    ]);
                }

                if ($assignment->status !== 'completed') {
                    $assignment->update(['status' => 'completed']);
                }

                // Update submission status to under_review if still pending
                if ($round && $round->submission_type === 'abstract' && $round->abstractSubmission) {
                    if ($round->abstractSubmission->status === 'pending') {
                        $round->abstractSubmission->update(['status' => 'under_review']);
                    }
                } elseif ($round && $round->submission_type === 'full_paper' && $round->fullPaper) {
                    if ($round->fullPaper->status === 'pending') {
                        $round->fullPaper->update(['status' => 'under_review']);
                    }
                }

                // Locking Logic: If all assignments in round are completed, lock round
                $totalAssignments = $round->assignments()->count();
                $completedAssignments = $round->assignments()->where('status', 'completed')->count();

                $requiredReviewers = ($round->submission_type === 'full_paper') ? 2 : 3;
                $isReadyToLock = ($round->round_number === 1)
                    ? ($totalAssignments === $requiredReviewers && $completedAssignments === $requiredReviewers)
                    : ($totalAssignments > 0 && $completedAssignments === $totalAssignments);

                if ($isReadyToLock && $round->status === 'open') {
                    $round->update(['status' => 'locked']);
                }

                return $review;
            });
        });
    }
}
