<?php

namespace App\Services\Admin;

use App\Models\AbstractSubmission;
use App\Models\ReviewRound;
use App\Models\ReviewAssignment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewAssignmentService
{
    public function assignReviewers(AbstractSubmission $abstract, array $reviewerIds): void
    {
        // Enforce max 3 reviewers per round specification
        $reviewerIds = array_values(array_unique(array_slice($reviewerIds, 0, 3)));

        Cache::lock("assign_reviewers_abstract_{$abstract->id}", 10)->block(5, function () use ($abstract, $reviewerIds) {
            DB::transaction(function () use ($abstract, $reviewerIds) {
            // Find or create the latest review round for this abstract
            $round = ReviewRound::where('submission_type', 'abstract')
                ->where('submission_id', $abstract->id)
                ->latest('round_number')
                ->first();

            if (!$round) {
                $round = ReviewRound::create([
                    'submission_type' => 'abstract',
                    'submission_id'   => $abstract->id,
                    'round_number'    => 1,
                    'status'          => 'open',
                ]);
            }

            // Get existing active assignments for this round with reviewer and review relations
            $existingAssignments = $round->assignments()
                ->with(['reviewer', 'review'])
                ->get()
                ->keyBy('reviewer_id');

            $existingReviewerIds = $existingAssignments->keys()->all();

            // Reviewers to add & remove
            $toAdd = array_diff($reviewerIds, $existingReviewerIds);
            $toRemove = array_diff($existingReviewerIds, $reviewerIds);

            // Safety Guard: Cannot remove a reviewer who has already submitted/completed their review
            foreach ($toRemove as $reviewerId) {
                $assignment = $existingAssignments->get($reviewerId);
                if ($assignment && ($assignment->status === 'completed' || $assignment->review !== null)) {
                    $reviewerName = $assignment->reviewer?->name ?? "ID #{$reviewerId}";
                    throw ValidationException::withMessages([
                        'reviewer_ids' => "Reviewer '{$reviewerName}' telah menyelesaikan penilaian untuk naskah ini dan tidak dapat dicabut.",
                    ]);
                }
            }

            // Soft-delete the removed assignments (preserves audit trail)
            if (!empty($toRemove)) {
                $round->assignments()->whereIn('reviewer_id', $toRemove)->delete();
            }

            // Add or restore assignments using Eloquent withTrashed pattern
            foreach ($toAdd as $reviewerId) {
                $assignment = ReviewAssignment::withTrashed()
                    ->where('review_round_id', $round->id)
                    ->where('reviewer_id', $reviewerId)
                    ->first();

                if ($assignment) {
                    if ($assignment->trashed()) {
                        $assignment->restore();
                    }
                    $assignment->update([
                        'status' => 'assigned',
                    ]);
                } else {
                    ReviewAssignment::create([
                        'review_round_id' => $round->id,
                        'reviewer_id'     => $reviewerId,
                        'status'          => 'assigned',
                    ]);
                }
            }

            // Sync abstract status: if pending and now assigned, move to under_review
            if (!empty($reviewerIds) && $abstract->status === 'pending') {
                $abstract->update(['status' => 'under_review']);
            }
        });
    });
}
}
