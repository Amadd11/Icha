<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(fn($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                ]);
            }, []),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
