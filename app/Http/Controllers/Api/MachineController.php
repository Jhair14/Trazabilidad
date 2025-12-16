<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\Request;
use App\Http\Requests\MachineRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\MachineResource;

class MachineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $machines = Machine::paginate();

        return MachineResource::collection($machines);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MachineRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Manual ID generation if not auto-increment
        if (empty($data['machine_id'])) {
            $maxId = Machine::max('maquina_id') ?? 0;
            $nextId = $maxId + 1;
            $data['machine_id'] = $nextId;
            
            // Generate code automatically if not provided
            if (empty($data['code'])) {
                $data['code'] = 'MAQ-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }
        }

        // Map English field names to Spanish column names
        $machineData = [
            'maquina_id' => $data['machine_id'],
            'codigo' => $data['code'],
            'nombre' => $data['name'],
            'descripcion' => $data['description'] ?? null,
            'imagen_url' => $data['image_url'] ?? null,
            'activo' => $data['active'] ?? true,
        ];

        $machine = Machine::create($machineData);

        return response()->json(new MachineResource($machine), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Machine $machine): JsonResponse
    {
        return response()->json(new MachineResource($machine));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MachineRequest $request, Machine $machine): JsonResponse
    {
        $machine->update($request->validated());

        return response()->json(new MachineResource($machine));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(Machine $machine): Response
    {
        $machine->delete();

        return response()->noContent();
    }
}