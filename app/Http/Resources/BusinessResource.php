<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\BusinessServiceResource;

class BusinessResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'address' => $this->address,
            'coordinates' => $this->coordinates,
            'logo' => $this->logo,
            'images' => $this->images ? json_decode($this->images) : [],
            'schedule' => $this->schedule ? json_decode($this->schedule) : null,
            'aforo' => $this->aforo,
            'created_at' => $this->created_at->format('Y-m-d H:i'),

            // 🔹 Información del propietario (si está cargada)
            'owner' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? null,
                'email' => $this->user->email ?? null,
            ],

            // 🔹 Servicios del negocio (si están cargados con ->load('services'))
            'services' => BusinessServiceResource::collection($this->whenLoaded('services')),

            // 🔹 Feedback promedio (si tienes una relación calculada)
            'average_rating' => $this->when(isset($this->average_rating), $this->average_rating),
        ];
    }
}
