<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcessMachineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'process_machine_id' => $this->process_machine_id,
            'process_id' => $this->process_id,
            'machine_id' => $this->machine_id,
            'step_order' => $this->step_order,
            'name' => $this->name,
            'description' => $this->description,
            'estimated_time' => $this->estimated_time,
            'machine' => $this->whenLoaded('machine', function () {
                return [
                    'machine_id' => $this->machine->machine_id,
                    'code' => $this->machine->code,
                    'name' => $this->machine->name,
                    'description' => $this->machine->description,
                    'image_url' => $this->machine->image_url,
                    'active' => $this->machine->active,
                ];
            }),
        ];
    }
}