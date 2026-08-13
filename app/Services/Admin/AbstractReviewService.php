<?php

namespace App\Services\Admin;

use App\Models\AbstractSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AbstractReviewService
{
    /**
     * Get abstract submissions filtered by status.
     */
    public function getAbstracts(?string $status = null): Collection
    {
        $confId = request()->query('conference_id') ?? session('admin_conference_id') ?? \App\Models\Conference::where('is_active', true)->first()?->id;

        $query = AbstractSubmission::with(['user', 'category', 'conference', 'reviewRounds.assignments.reviewer'])
            ->when($confId, fn($q) => $q->where('conference_id', $confId))
            ->latest();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        return $query->get();
    }

    /**
     * Process review decision for an abstract.
     */
    public function reviewAbstract(AbstractSubmission $abstract, User $reviewer, array $data): bool
    {
        return $abstract->update([
            'status' => $data['status'],
            'review_notes' => $data['review_notes'] ?? null,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }
}
