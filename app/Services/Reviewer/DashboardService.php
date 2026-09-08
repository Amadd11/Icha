<?php

namespace App\Services\Reviewer;

use App\Models\ReviewAssignment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    /**
     * Get dashboard statistics for the currently logged-in reviewer based on consolidated submissions.
     */
    public function getStats(?Collection $assignments = null, ?string $submissionType = null): array
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

        $assignments = $assignments ?? $this->getAssignments($submissionType);

        return [
            'total_assigned'     => $assignments->count(),
            'pending_reviews'    => $assignments->where('status', 'assigned')->count(),
            'completed_reviews'  => $assignments->where('status', 'completed')->count(),
            'upcoming_deadlines' => 0,
        ];
    }

    /**
     * Get all active assignments for the currently logged-in reviewer, consolidated by submission.
     * Each unique manuscript appears only once (showing the latest round assignment),
     * with past round reviews attached to `previous_history`.
     */
    public function getAssignments(?string $submissionType = null): Collection
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return collect();
        }

        $allAssignments = ReviewAssignment::with([
            'round.abstractSubmission.category',
            'review',
        ])
            ->where('reviewer_id', $user->id)
            ->whereHas('round', function ($q) {
                $q->where('submission_type', 'abstract')
                  ->whereHas('abstractSubmission');
            })
            ->get();

        // Group assignments by unique abstract submission
        $grouped = $allAssignments->groupBy(function ($assignment) {
            return (string) ($assignment->round?->submission_id ?? $assignment->id);
        });

        $consolidated = $grouped->map(function ($group) {
            // Sort assignments in this submission group descending by round_number, then id
            $sorted = $group->sortByDesc(function ($assignment) {
                return [
                    $assignment->round?->round_number ?? 1,
                    $assignment->id,
                ];
            })->values();

            /** @var ReviewAssignment $latestAssignment */
            $latestAssignment = $sorted->first();

            // Collect historical reviews from past rounds (older than latestAssignment)
            $previousHistory = $sorted->slice(1)->map(function ($pastAssignment) {
                return [
                    'assignment_id'     => $pastAssignment->id,
                    'round_number'      => $pastAssignment->round?->round_number ?? 1,
                    'round_status'      => $pastAssignment->round?->status,
                    'assignment_status' => $pastAssignment->status,
                    'review'            => $pastAssignment->review ? [
                        'id'               => $pastAssignment->review->id,
                        'score_criteria_1' => $pastAssignment->review->score_criteria_1,
                        'score_criteria_2' => $pastAssignment->review->score_criteria_2,
                        'total_score'      => ($pastAssignment->review->score_criteria_1 ?? 0) + ($pastAssignment->review->score_criteria_2 ?? 0),
                        'recommendation'   => $pastAssignment->review->recommendation,
                        'summary'          => $pastAssignment->review->summary,
                        'updated_at'       => $pastAssignment->review->updated_at?->format('d M Y H:i'),
                    ] : null,
                ];
            })->values()->all();

            $latestAssignment->previous_history = $previousHistory;

            return $latestAssignment;
        })->values();

        // Sort consolidated submissions: pending reviews first, then latest assignment id descending
        return $consolidated->sort(function ($a, $b) {
            $aPending = $a->status === 'assigned' ? 1 : 0;
            $bPending = $b->status === 'assigned' ? 1 : 0;
            if ($bPending !== $aPending) {
                return $bPending <=> $aPending;
            }
            return $b->id <=> $a->id;
        })->values();
    }
}
