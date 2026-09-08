<?php

namespace App\Services\Admin;

use App\Models\AbstractSubmission;
use App\Models\FullPaper;
use App\Models\ReviewRound;
use App\Models\ReviewAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewAssignmentService
{
    public function assignReviewers(AbstractSubmission $abstract, array $reviewerIds): void
    {
        $reviewerIds = array_values(array_unique($reviewerIds));

        $latestRound = ReviewRound::where('submission_type', 'abstract')
            ->where('submission_id', $abstract->id)
            ->latest('round_number')
            ->first();

        if ($abstract->status === 'revision_required') {
            throw ValidationException::withMessages([
                'reviewer_ids' => 'Naskah sedang menunggu unggahan berkas revisi dari penulis. Penugasan reviewer tidak dapat diubah.',
            ]);
        }

        $isRound1 = !$latestRound || $latestRound->round_number === 1;

        if (!$isRound1) {
            throw ValidationException::withMessages([
                'reviewer_ids' => 'Penugasan reviewer pada tahap revisi terkunci otomatis dan hanya dapat dinilai oleh reviewer yang meminta revisi.',
            ]);
        }

        if (count($reviewerIds) !== 3) {
            throw ValidationException::withMessages([
                'reviewer_ids' => 'Tepat tiga reviewer harus ditugaskan untuk telaah awal.',
            ]);
        }

        $invalidReviewerExists = User::whereIn('id', $reviewerIds)
            ->where('role', '!=', 'reviewer')
            ->exists();

        if ($invalidReviewerExists) {
            throw ValidationException::withMessages([
                'reviewer_ids' => 'Semua reviewer yang ditugaskan harus memiliki role reviewer.',
            ]);
        }

        // Enforce conflict of interest: Author cannot review their own submission
        if (in_array($abstract->user_id, $reviewerIds)) {
            throw ValidationException::withMessages([
                'reviewer_ids' => 'Penulis (author) naskah ini tidak dapat ditugaskan sebagai reviewer untuk naskahnya sendiri.',
            ]);
        }

        // Enforce category match: Reviewer must have expertise in the abstract's category
        if ($abstract->category_id) {
            $unmatchedReviewers = User::whereIn('id', $reviewerIds)
                ->whereDoesntHave('categories', function ($query) use ($abstract) {
                    $query->where('categories.id', $abstract->category_id);
                })
                ->pluck('name')
                ->toArray();

            if (!empty($unmatchedReviewers)) {
                $names = implode(', ', $unmatchedReviewers);
                throw ValidationException::withMessages([
                    'reviewer_ids' => "Reviewer ({$names}) tidak memiliki bidang kepakaran yang sesuai dengan kategori naskah ini.",
                ]);
            }
        }

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
                } elseif ($round->status === 'pending') {
                    $round->update(['status' => 'open']);
                } elseif (in_array($round->status, ['locked', 'completed'], true)) {
                    throw ValidationException::withMessages([
                        'reviewer_ids' => 'Reviewer tidak dapat diubah setelah round dikunci atau selesai.',
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

    public function assignPaperReviewers(FullPaper $paper, array $reviewerIds): void
    {
        $reviewerIds = array_values(array_unique($reviewerIds));

        $latestRound = ReviewRound::where('submission_type', 'full_paper')
            ->where('submission_id', $paper->id)
            ->latest('round_number')
            ->first();

        if ($paper->status === 'revision_required') {
            throw ValidationException::withMessages([
                'reviewer_ids' => 'Naskah sedang menunggu unggahan berkas revisi dari penulis. Penugasan reviewer tidak dapat diubah.',
            ]);
        }

        $isRound1 = !$latestRound || $latestRound->round_number === 1;

        if (!$isRound1) {
            throw ValidationException::withMessages([
                'reviewer_ids' => 'Penugasan reviewer pada tahap revisi terkunci otomatis dan hanya dapat dinilai oleh reviewer yang meminta revisi.',
            ]);
        }

        if (count($reviewerIds) !== 2) {
            throw ValidationException::withMessages([
                'reviewer_ids' => 'Tepat dua reviewer harus ditugaskan untuk telaah full paper.',
            ]);
        }

        $invalidReviewerExists = User::whereIn('id', $reviewerIds)
            ->where('role', '!=', 'reviewer')
            ->exists();

        if ($invalidReviewerExists) {
            throw ValidationException::withMessages([
                'reviewer_ids' => 'Semua reviewer yang ditugaskan harus memiliki role reviewer.',
            ]);
        }

        // Enforce conflict of interest: Author cannot review their own submission
        $paperAuthorId = $paper->user_id ?? $paper->abstract?->user_id;
        if ($paperAuthorId && in_array($paperAuthorId, $reviewerIds)) {
            throw ValidationException::withMessages([
                'reviewer_ids' => 'Penulis (author) naskah ini tidak dapat ditugaskan sebagai reviewer untuk naskahnya sendiri.',
            ]);
        }

        // Enforce category match: Reviewer must have expertise in the paper's category
        $categoryId = $paper->abstract?->category_id;
        if ($categoryId) {
            $unmatchedReviewers = User::whereIn('id', $reviewerIds)
                ->whereDoesntHave('categories', function ($query) use ($categoryId) {
                    $query->where('categories.id', $categoryId);
                })
                ->pluck('name')
                ->toArray();

            if (!empty($unmatchedReviewers)) {
                $names = implode(', ', $unmatchedReviewers);
                throw ValidationException::withMessages([
                    'reviewer_ids' => "Reviewer ({$names}) tidak memiliki bidang kepakaran yang sesuai dengan kategori naskah ini.",
                ]);
            }
        }

        Cache::lock("assign_reviewers_paper_{$paper->id}", 10)->block(5, function () use ($paper, $reviewerIds) {
            DB::transaction(function () use ($paper, $reviewerIds) {
                $round = ReviewRound::where('submission_type', 'full_paper')
                    ->where('submission_id', $paper->id)
                    ->latest('round_number')
                    ->first();

                if (!$round) {
                    $round = ReviewRound::create([
                        'submission_type' => 'full_paper',
                        'submission_id'   => $paper->id,
                        'round_number'    => 1,
                        'status'          => 'open',
                    ]);
                } elseif ($round->status === 'pending') {
                    $round->update(['status' => 'open']);
                } elseif (in_array($round->status, ['locked', 'completed'], true)) {
                    throw ValidationException::withMessages([
                        'reviewer_ids' => 'Reviewer tidak dapat diubah setelah round dikunci atau selesai.',
                    ]);
                }

                $existingAssignments = $round->assignments()
                    ->with(['reviewer', 'review'])
                    ->get()
                    ->keyBy('reviewer_id');

                $existingReviewerIds = $existingAssignments->keys()->all();

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

                if (!empty($toRemove)) {
                    $round->assignments()->whereIn('reviewer_id', $toRemove)->delete();
                }

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

                // Sync paper status: if pending and now assigned, move to under_review
                if (!empty($reviewerIds) && $paper->status === 'pending') {
                    $paper->update(['status' => 'under_review']);
                }
            });
        });
    }
}
