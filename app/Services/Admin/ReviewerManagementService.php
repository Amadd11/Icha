<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            // Default password for testing
            'password' => Hash::make('password'),
            'role' => 'reviewer',
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

        $reviewer->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

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

        $reviewer->delete();
    }
}
