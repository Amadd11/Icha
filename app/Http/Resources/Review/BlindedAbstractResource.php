<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlindedAbstractResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * CRITICAL: Strictly forbids exposing author identity (name, email, phone, institution).
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
            'file_path' => $this->file_path,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
