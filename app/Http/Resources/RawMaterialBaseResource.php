<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RawMaterialBaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'material_id' => $this->material_id,
            'category_id' => $this->category_id,
            'unit_id' => $this->unit_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'available_quantity' => $this->available_quantity,
            'minimum_stock' => $this->minimum_stock,
            'maximum_stock' => $this->maximum_stock,
            'active' => $this->active,
            'category' => $this->whenLoaded('category'),
            'unit' => $this->whenLoaded('unit'),
        ];
    }
}

