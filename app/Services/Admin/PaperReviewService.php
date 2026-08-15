<?php

namespace App\Services\Admin;

use App\Models\FullPaper;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaperReviewService
{
    /**
     * Get full paper submissions filtered by status with pagination.
     */
    public function getPapers(?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $confId = request()->query('conference_id') ?? session('admin_conference_id') ?? \App\Models\Conference::where('is_active', true)->first()?->id;

        $query = FullPaper::with(['user', 'abstract.category', 'conference', 'reviewer'])
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
        return $paper->update([
            'status' => $data['status'],
            'review_notes' => $data['review_notes'] ?? null,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }
}
