<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MachineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        if (isset($data['imagen_url']) && $data['imagen_url']) {
            if (!str_starts_with($data['imagen_url'], 'http')) {
                $data['imagen_url'] = url($data['imagen_url']);
            }
        }

        return $data;
    }
}