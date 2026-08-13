<?php

namespace App\Services\Admin;

use App\Models\FullPaper;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PaperReviewService
{
    /**
     * Get full paper submissions filtered by status.
     */
    public function getPapers(?string $status = null): Collection
    {
        $confId = request()->query('conference_id') ?? session('admin_conference_id') ?? \App\Models\Conference::where('is_active', true)->first()?->id;

        $query = FullPaper::with(['user', 'abstract.category', 'conference', 'reviewer'])
            ->when($confId, fn($q) => $q->where('conference_id', $confId))
            ->latest();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        return $query->get();
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
