<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Process;
use Illuminate\Http\Request;
use App\Http\Requests\ProcessRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ProcessResource;

class ProcessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $processs = Process::paginate();

        return ProcessResource::collection($processs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProcessRequest $request): JsonResponse
    {
        $process = Process::create($request->validated());

        return response()->json(new ProcessResource($process), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Process $process): JsonResponse
    {
        return response()->json(new ProcessResource($process));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProcessRequest $request, Process $process): JsonResponse
    {
        $process->update($request->validated());

        return response()->json(new ProcessResource($process));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(Process $process): Response
    {
        $process->delete();

        return response()->noContent();
    }
}