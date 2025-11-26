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
        $machine = Machine::create($request->validated());

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