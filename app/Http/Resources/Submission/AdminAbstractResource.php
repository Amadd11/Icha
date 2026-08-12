<?php

namespace App\Http\Resources\Submission;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminAbstractResource extends JsonResource
{
    /**
     * Includes author identity for Admin view.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'abstract_code' => $this->abstract_code,
            'title' => $this->title,
            'abstract_text' => $this->abstract_text,
            'keywords' => $this->keywords,
            'presentation_type' => $this->presentation_type,
            'status' => $this->status,
            'file_path' => $this->file_path,
            'category_id' => $this->category_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'review_rounds' => $this->whenLoaded('reviewRounds'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
