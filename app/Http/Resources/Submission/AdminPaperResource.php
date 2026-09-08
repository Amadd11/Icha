<?php

namespace App\Http\Resources\Submission;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminPaperResource extends JsonResource
{
    /**
     * Includes author identity, track category, manuscript file, and reviewer feedback for Admin view.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'paper_code'    => $this->paper_code ?? ('FP-' . str_pad($this->id, 3, '0', STR_PAD_LEFT)),
            'title'         => $this->title ?? $this->abstract?->title,
            'status'        => $this->status,
            'review_notes'  => $this->review_notes,
            'file_path'     => $this->file_path,
            'abstract_id'   => $this->abstract_id,
            'user_id'       => $this->user_id ?? $this->abstract?->user_id,
            'user'          => $this->whenLoaded('user', function () {
                return [
                    'id'      => $this->user->id,
                    'name'    => $this->user->name,
                    'email'   => $this->user->email,
                    'role'    => $this->user->role,
                    'profile' => $this->user->profile ? [
                        'institution' => $this->user->profile->institution,
                    ] : null,
                ];
            }, function () {
                $user = $this->abstract?->user;
                return $user ? [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'role'    => $user->role,
                    'profile' => $user->profile ? [
                        'institution' => $user->profile->institution,
                    ] : null,
                ] : null;
            }),
            'abstract'      => $this->whenLoaded('abstract', function () {
                return [
                    'id'            => $this->abstract->id,
                    'abstract_code' => $this->abstract->abstract_code,
                    'title'         => $this->abstract->title,
                    'abstract_text' => $this->abstract->abstract_text,
                    'category_id'   => $this->abstract->category_id,
                    'category'      => $this->abstract->category ? [
                        'id'   => $this->abstract->category->id,
                        'name' => $this->abstract->category->name,
                    ] : null,
                ];
            }),
            'category'      => $this->abstract?->category ? [
                'id'   => $this->abstract->category->id,
                'name' => $this->abstract->category->name,
            ] : null,
            'category_id'   => $this->abstract?->category_id,
            'review_rounds' => $this->whenLoaded('reviewRounds', function () {
                return $this->reviewRounds->map(function ($round) {
                    return [
                        'id'           => $round->id,
                        'round_number' => $round->round_number ?? 1,
                        'status'       => $round->status,
                        'assignments'  => $round->assignments ? $round->assignments->map(function ($a) {
                            $review = $a->review;
                            return [
                                'id'               => $a->id,
                                'reviewer_id'      => $a->reviewer_id,
                                'reviewer_name'    => $a->reviewer->name ?? 'Reviewer',
                                'status'           => $a->status,
                                'recommendation'   => $review?->recommendation,
                                'comments'         => $review?->summary,
                                'score_criteria_1' => $review?->score_criteria_1,
                                'score_criteria_2' => $review?->score_criteria_2,
                                'total_score'      => $review?->total_score,
                                'reviewed_at'      => $review?->created_at?->toIso8601String(),
                            ];
                        }) : [],
                    ];
                });
            }),
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
