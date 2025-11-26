<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RawMaterialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'raw_material_id' => $this->raw_material_id,
            'material_id' => $this->material_id,
            'supplier_id' => $this->supplier_id,
            'supplier_batch' => $this->supplier_batch,
            'invoice_number' => $this->invoice_number,
            'receipt_date' => $this->receipt_date,
            'expiration_date' => $this->expiration_date,
            'quantity' => $this->quantity,
            'available_quantity' => $this->available_quantity,
            'receipt_conformity' => $this->receipt_conformity,
            'observations' => $this->observations,
            'material_base' => $this->whenLoaded('materialBase'),
            'supplier' => $this->whenLoaded('supplier'),
        ];
    }
}

