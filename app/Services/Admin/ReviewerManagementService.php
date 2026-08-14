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
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make('password'),
            'role'     => 'reviewer',
        ]);

        if (!empty($data['category_ids'])) {
            $user->categories()->sync($data['category_ids']);

            // Auto-assign existing abstracts in these categories
            $abstracts = AbstractSubmission::whereIn('category_id', $data['category_ids'])->get();
            foreach ($abstracts as $abstract) {
                $round = ReviewRound::firstOrCreate(
                    [
                        'submission_type' => 'abstract',
                        'submission_id'   => $abstract->id,
                    ],
                    [
                        'status' => 'pending',
                    ]
                );

                ReviewAssignment::firstOrCreate(
                    [
                        'review_round_id' => $round->id,
                        'reviewer_id'     => $user->id,
                    ],
                    [
                        'status' => 'assigned',
                    ]
                );
            }
        }

        return $user;
    }

    public function updateReviewer(User $reviewer, array $data): User
    {
        if ($reviewer->role !== 'reviewer') {
            throw new \InvalidArgumentException('User is not a reviewer.');
        }

        $reviewer->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        if (isset($data['category_ids'])) {
            $reviewer->categories()->sync($data['category_ids']);

            // Auto-assign existing abstracts in these categories
            $abstracts = AbstractSubmission::whereIn('category_id', $data['category_ids'])->get();
            foreach ($abstracts as $abstract) {
                $round = ReviewRound::firstOrCreate(
                    [
                        'submission_type' => 'abstract',
                        'submission_id'   => $abstract->id,
                    ],
                    [
                        'status' => 'pending',
                    ]
                );

                ReviewAssignment::firstOrCreate(
                    [
                        'review_round_id' => $round->id,
                        'reviewer_id'     => $reviewer->id,
                    ],
                    [
                        'status' => 'assigned',
                    ]
                );
            }
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

        $reviewer->categories()->detach();
        $reviewer->delete();
    }
}
