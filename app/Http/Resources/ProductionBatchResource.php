<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionBatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'batch_id' => $this->batch_id,
            'order_id' => $this->order_id,
            'batch_code' => $this->batch_code,
            'name' => $this->name,
            'creation_date' => $this->creation_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'target_quantity' => $this->target_quantity,
            'produced_quantity' => $this->produced_quantity,
            'observations' => $this->observations,
            'order' => $this->whenLoaded('order'),
            'raw_materials' => BatchRawMaterialResource::collection($this->whenLoaded('rawMaterials')),
        ];
    }
}

