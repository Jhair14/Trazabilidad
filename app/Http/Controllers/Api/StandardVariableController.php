<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StandardVariable;
use Illuminate\Http\Request;
use App\Http\Requests\StandardVariableRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\StandardVariableResource;

class StandardVariableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $standardVariables = StandardVariable::paginate();

        return StandardVariableResource::collection($standardVariables);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StandardVariableRequest $request): JsonResponse
    {
        $standardVariable = StandardVariable::create($request->validated());

        return response()->json(new StandardVariableResource($standardVariable), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(StandardVariable $standardVariable): JsonResponse
    {
        return response()->json(new StandardVariableResource($standardVariable));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StandardVariableRequest $request, StandardVariable $standardVariable): JsonResponse
    {
        $standardVariable->update($request->validated());

        return response()->json(new StandardVariableResource($standardVariable));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(StandardVariable $standardVariable): Response
    {
        $standardVariable->delete();

        return response()->noContent();
    }
}