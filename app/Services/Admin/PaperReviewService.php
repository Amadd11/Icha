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
        $query = FullPaper::with(['user', 'abstract', 'conference', 'reviewer'])
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
