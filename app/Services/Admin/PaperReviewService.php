<?php

namespace App\Services\Admin;

use App\Models\Conference;
use App\Models\FullPaper;
use App\Models\User;
use App\Models\ReviewRound;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class PaperReviewService
{
    /**
     * Get full paper submissions filtered by status with pagination.
     */
    public function getPapers(?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $confId = request()->query('conference_id') ?? session('admin_conference_id') ?? Conference::where('is_active', true)->first()?->id;

        $query = FullPaper::with([
            'user',
            'abstract.category',
            'conference',
            'reviewer',
            'reviewRounds.assignments.reviewer',
            'reviewRounds.assignments.review',
        ])
            ->when($confId, fn($q) => $q->where('conference_id', $confId))
            ->latest();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Process review decision for a full paper.
     */
    public function reviewPaper(FullPaper $paper, User $reviewer, array $data): bool
    {
        $round = null;
        if (in_array($data['status'], ['accepted', 'revision_required', 'rejected'], true)) {
            $round = ReviewRound::where('submission_type', 'full_paper')
                ->where('submission_id', $paper->id)
                ->latest('round_number')
                ->first();

            if ($round) {
                if (!in_array($round->status, ['locked', 'completed'], true)) {
                    throw ValidationException::withMessages([
                        'status' => 'Final decision hanya dapat dibuat setelah review round selesai dan terkunci (locked).',
                    ]);
                }

                $totalAssignments = $round->assignments()->count();
                $completedAssignments = $round->assignments()->where('status', 'completed')->count();

                if ($round->round_number === 1) {
                    if ($totalAssignments !== 2 || $completedAssignments !== 2) {
                        throw ValidationException::withMessages([
                            'status' => 'Final decision memerlukan tepat 2 reviewer yang telah menyelesaikan review.',
                        ]);
                    }
                } else {
                    if ($totalAssignments === 0 || $completedAssignments < $totalAssignments) {
                        throw ValidationException::withMessages([
                            'status' => 'Final decision memerlukan seluruh reviewer pada round revisi ini menyelesaikan review.',
                        ]);
                    }
                }
            }
        }

        $updated = $paper->update([
            'status'       => $data['status'],
            'review_notes' => $data['review_notes'] ?? null,
            'reviewed_by'  => $reviewer->id,
            'reviewed_at'  => now(),
        ]);

        if ($round && $round->status === 'locked') {
            $round->update(['status' => 'completed']);
        }

        return $updated;
    }
}
