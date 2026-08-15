<?php

namespace App\Services\Admin;

use App\Models\AbstractSubmission;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AbstractReviewService
{
    /**
     * Get abstract submissions filtered by status with pagination.
     */
    public function getAbstracts(?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $confId = request()->query('conference_id') ?? session('admin_conference_id') ?? \App\Models\Conference::where('is_active', true)->first()?->id;

        $query = AbstractSubmission::with([
            'user.profile',
            'category',
            'conference',
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
     * Process review decision for an abstract.
     */
    public function reviewAbstract(AbstractSubmission $abstract, User $reviewer, array $data): bool
    {
        $updateData = [
            'status'       => $data['status'],
            'review_notes' => $data['review_notes'] ?? null,
            'reviewed_by'  => $reviewer->id,
            'reviewed_at'  => now(),
        ];

        if ($data['status'] === 'accepted' && !empty($data['presentation_type'])) {
            $updateData['presentation_type'] = $data['presentation_type'];
        }

        return $abstract->update($updateData);
    }
}
