<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'review_round_id' => $this->review_round_id,
            'reviewer_id' => $this->reviewer_id,
            'status' => $this->status,
            'round' => new ReviewRoundResource($this->whenLoaded('round')),
            'review' => new ReviewResource($this->whenLoaded('review')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
