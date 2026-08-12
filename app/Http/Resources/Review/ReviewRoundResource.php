<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewRoundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'submission_type' => $this->submission_type,
            'submission_id' => $this->submission_id,
            'status' => $this->status,
            'submission' => $this->whenLoaded('abstractSubmission', function () {
                return new BlindedAbstractResource($this->abstractSubmission);
            }),
            'assignments' => ReviewAssignmentResource::collection($this->whenLoaded('assignments')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
