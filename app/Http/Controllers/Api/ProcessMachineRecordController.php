<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcessMachineRecord;
use Illuminate\Http\Request;
use App\Http\Requests\ProcessMachineRecordRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ProcessMachineRecordResource;

class ProcessMachineRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $processMachineRecords = ProcessMachineRecord::paginate();

        return ProcessMachineRecordResource::collection($processMachineRecords);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProcessMachineRecordRequest $request): JsonResponse
    {
        $processMachineRecord = ProcessMachineRecord::create($request->validated());

        return response()->json(new ProcessMachineRecordResource($processMachineRecord), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProcessMachineRecord $processMachineRecord): JsonResponse
    {
        return response()->json(new ProcessMachineRecordResource($processMachineRecord));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProcessMachineRecordRequest $request, ProcessMachineRecord $processMachineRecord): JsonResponse
    {
        $processMachineRecord->update($request->validated());

        return response()->json(new ProcessMachineRecordResource($processMachineRecord));
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(ProcessMachineRecord $processMachineRecord): Response
    {
        $processMachineRecord->delete();

        return response()->noContent();
    }
}