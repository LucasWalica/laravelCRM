<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
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
            'status' => $this->status,
            'time_start' => $this->time_start->format('Y-m-d H:i'),
            'estimated_time_end' => $this->estimated_time_end
                ? $this->estimated_time_end->format('Y-m-d H:i')
                : null,
            'aforo' => $this->aforo,
            'created_at' => $this->created_at->format('Y-m-d H:i'),

            // 🔹 Información del cliente (si está cargada)
            'client' => $this->whenLoaded('client', function () {
                return [
                    'id' => $this->client->id,
                    'name' => $this->client->name,
                    'surname1' => $this->client->surname1,
                    'phone' => $this->client->phone,
                    'email' => $this->client->email,
                ];
            }),

            // 🔹 Información del servicio (si está cargada)
            'service' => $this->whenLoaded('service', function () {
                return [
                    'id' => $this->service->id,
                    'name' => $this->service->name,
                    'price' => $this->service->price,
                    'time_estimation' => $this->service->time_estimation,
                ];
            }),

            // 🔹 Información del negocio (a través del servicio)
            'business' => $this->whenLoaded('service', function () {
                return [
                    'id' => $this->service->business->id ?? null,
                    'name' => $this->service->business->name ?? null,
                ];
            }),
        ];
    }
}
