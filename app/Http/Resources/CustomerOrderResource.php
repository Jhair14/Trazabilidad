<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->order_id,
            'customer_id' => $this->customer_id,
            'order_number' => $this->order_number,
            'creation_date' => $this->creation_date,
            'delivery_date' => $this->delivery_date,
            'description' => $this->description,
            'observations' => $this->observations,
            'customer' => $this->whenLoaded('customer'),
            'batches' => ProductionBatchResource::collection($this->whenLoaded('batches')),
        ];
    }
}

