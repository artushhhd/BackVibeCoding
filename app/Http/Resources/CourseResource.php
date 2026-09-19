<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => (float) $this->price,
            'image_url' => $this->image_url,
            'author' => $this->whenLoaded('author', function () {
                return [
                    'id' => $this->author->id,
                    'name' => $this->author->name,
                ];
            }),
            'likes_count' => (int) ($this->likes_count ?? 0),
            'liked_by_current_user' => (bool) ($this->liked_by_current_user ?? false),
            'purchased_by_current_user' => (bool) ($this->purchased_by_current_user ?? false),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
