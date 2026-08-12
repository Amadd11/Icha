<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'review_assignment_id' => $this->review_assignment_id,
            'score_criteria_1' => $this->score_criteria_1,
            'score_criteria_2' => $this->score_criteria_2,
            'total_score' => $this->total_score,
            'recommendation' => $this->recommendation,
            'summary' => $this->summary,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
