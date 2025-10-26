<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessFeedbackResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'stars' => $this->stars,
            'created_at' => $this->created_at->format('Y-m-d H:i'),

            // 🔹 Relaciones resumidas
            'business' => [
                'id' => $this->business->id ?? null,
                'name' => $this->business->name ?? null,
            ],

            'user' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? null,
                'surname1' => $this->user->surname1 ?? null,
            ],
        ];
    }
}
