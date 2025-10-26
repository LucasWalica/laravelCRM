<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessServiceResource extends JsonResource
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
            'business_id' => $this->business_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'time_estimation' => $this->time_estimation,
            'aforo' => $this->aforo,
            'created_at' => $this->created_at->format('Y-m-d H:i'),

            // 🔹 Si el servicio está cargado con su negocio
            'business' => $this->whenLoaded('business', function () {
                return [
                    'id' => $this->business->id,
                    'name' => $this->business->name,
                ];
            }),
        ];
    }
}
