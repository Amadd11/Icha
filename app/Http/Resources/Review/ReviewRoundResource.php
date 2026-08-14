<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewRoundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $submission = null;

        if ($this->relationLoaded('abstractSubmission') && $this->abstractSubmission) {
            $submission = BlindedAbstractResource::make($this->abstractSubmission);
        } elseif ($this->relationLoaded('fullPaper') && $this->fullPaper) {
            $submission = $this->fullPaper;
        } elseif ($this->submission) {
            $submission = BlindedAbstractResource::make($this->submission);
        }

        return [
            'id'              => $this->id,
            'submission_type' => $this->submission_type,
            'submission_id'   => $this->submission_id,
            'status'          => $this->status,
            'submission'      => $submission,
            'assignments'     => ReviewAssignmentResource::collection($this->whenLoaded('assignments')),
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
