<?php

namespace App\Services\Admin;

use App\Models\AbstractSubmission;
use App\Models\Conference;
use App\Models\User;
use App\Models\ReviewRound;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class AbstractReviewService
{
    /**
     * Get abstract submissions filtered by status with pagination.
     */
    public function getAbstracts(?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $confId = request()->query('conference_id') ?? session('admin_conference_id') ?? Conference::where('is_active', true)->first()?->id;

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
        $round = $this->assertFinalDecisionAllowed($abstract, $data['status'], $data['presentation_type'] ?? null);

        $updateData = [
            'status'       => $data['status'],
            'review_notes' => $data['review_notes'] ?? null,
            'reviewed_by'  => $reviewer->id,
            'reviewed_at'  => now(),
        ];

        if ($data['status'] === 'accepted') {
            $presentationType = !empty($data['presentation_type'])
                ? strtolower($data['presentation_type'])
                : ($round ? strtolower((string) $round->finalRecommendation()) : 'oral');
            $updateData['presentation_type'] = in_array($presentationType, ['oral', 'poster'], true) ? $presentationType : 'oral';
        }

        $updated = $abstract->update($updateData);

        if ($round && $round->status === 'locked') {
            $round->update(['status' => 'completed']);
        }

        return $updated;
    }

    private function assertFinalDecisionAllowed(AbstractSubmission $abstract, string $status, ?string $presentationType): ?ReviewRound
    {
        if (!in_array($status, ['accepted', 'revision_required', 'rejected'], true)) {
            return null;
        }

        $round = ReviewRound::where('submission_type', 'abstract')
            ->where('submission_id', $abstract->id)
            ->latest('round_number')
            ->first();

        if (!$round || !in_array($round->status, ['locked', 'completed'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Final decision hanya dapat dibuat setelah review round selesai dan terkunci (locked).',
            ]);
        }

        $totalAssignments = $round->assignments()->count();
        $completedAssignments = $round->assignments()->where('status', 'completed')->count();

        if ($round->round_number === 1) {
            if ($totalAssignments !== 3 || $completedAssignments !== 3) {
                throw ValidationException::withMessages([
                    'status' => 'Final decision memerlukan tepat 3 reviewer yang telah menyelesaikan review.',
                ]);
            }
        } else {
            if ($totalAssignments === 0 || $completedAssignments < $totalAssignments) {
                throw ValidationException::withMessages([
                    'status' => 'Final decision memerlukan seluruh reviewer pada round revisi ini menyelesaikan review.',
                ]);
            }
        }

        return $round;
    }
}
