<?php

namespace App\Services\Admin;

use App\Models\AbstractSubmission;
use App\Models\Category;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class ReviewerManagementService
{
    public function getReviewers(): Collection
    {
        return User::where('role', 'reviewer')
            ->with('categories')
            ->orderBy('name')
            ->get();
    }

    public function getCategories(): Collection
    {
        return Category::orderBy('name')->get();
    }

    public function createReviewer(array $data): User
    {
        $existing = User::withTrashed()->where('email', $data['email'])->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->update([
                'name'     => $data['name'],
                'password' => Hash::make($data['password']),
                'role'     => 'reviewer',
            ]);

            if (!empty($data['category_ids'])) {
                $existing->categories()->sync($data['category_ids']);
            }

            return $existing;
        }

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'reviewer',
        ]);

        if (!empty($data['category_ids'])) {
            $user->categories()->sync($data['category_ids']);
        }

        return $user;
    }

    public function updateReviewer(User $reviewer, array $data): User
    {
        if ($reviewer->role !== 'reviewer') {
            throw new \InvalidArgumentException('User is not a reviewer.');
        }

        $updateData = [
            'name'  => $data['name'],
            'email' => $data['email'],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $reviewer->update($updateData);

        if (isset($data['category_ids'])) {
            $reviewer->categories()->sync($data['category_ids']);
        } else {
            $reviewer->categories()->detach();
        }

        return $reviewer;
    }

    public function deleteReviewer(User $reviewer): void
    {
        if ($reviewer->role !== 'reviewer') {
            throw new \InvalidArgumentException('User is not a reviewer.');
        }

        $hasActiveAssignments = ReviewAssignment::where('reviewer_id', $reviewer->id)
            ->where('status', 'assigned')
            ->exists();

        if ($hasActiveAssignments) {
            throw new \InvalidArgumentException('Tidak dapat menghapus reviewer yang masih memiliki tugas penilaian review aktif pada naskah.');
        }

        $reviewer->categories()->detach();
        $reviewer->delete();
    }
}
