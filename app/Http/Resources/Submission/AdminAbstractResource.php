<?php

namespace App\Http\Resources\Submission;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminAbstractResource extends JsonResource
{
    /**
     * Includes author identity, category track, and reviewer feedback for Admin view.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'abstract_code'     => $this->abstract_code,
            'title'             => $this->title,
            'abstract_text'     => $this->abstract_text,
            'keywords'          => $this->keywords,
            'presentation_type' => $this->presentation_type,
            'status'            => $this->status,
            'review_notes'      => $this->review_notes,
            'file_path'         => $this->file_path,
            'category_id'       => $this->category_id,
            'user'              => $this->whenLoaded('user', function () {
                return [
                    'id'    => $this->user->id,
                    'name'  => $this->user->name,
                    'email' => $this->user->email,
                    'role'  => $this->user->role,
                ];
            }),
            'category'          => $this->whenLoaded('category', function () {
                return [
                    'id'   => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'review_rounds'     => $this->whenLoaded('reviewRounds', function () {
                return $this->reviewRounds->map(function ($round) {
                    return [
                        'id'           => $round->id,
                        'round_number' => $round->round_number,
                        'status'       => $round->status,
                        'assignments'  => $round->assignments ? $round->assignments->map(function ($a) {
                            return [
                                'id'             => $a->id,
                                'reviewer_id'    => $a->reviewer_id,
                                'reviewer_name'  => $a->reviewer->name ?? 'Reviewer',
                                'recommendation' => $a->recommendation,
                                'comments'       => $a->comments,
                                'reviewed_at'    => $a->reviewed_at?->toIso8601String(),
                            ];
                        }) : [],
                    ];
                });
            }),
            'created_at'        => $this->created_at?->toIso8601String(),
        ];
    }
}
