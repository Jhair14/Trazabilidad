<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Process;
use App\Models\ProcessMachine;
use Illuminate\Http\Request;
use App\Http\Requests\ProcessRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ProcessResource;
use Illuminate\Support\Facades\DB;

class ProcessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Process::query();
        
        // Optionally include process machines
        if ($request->query('include') === 'machines') {
            $query->with(['processMachines.machine']);
        }
        
        $processes = $query->paginate();

        return ProcessResource::collection($processes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProcessRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            $data = $request->validated();
            \Log::info('Process creation - validated data', ['data' => $data]);
            
            // Manual ID generation if not auto-increment
            if (empty($data['process_id'])) {
                $maxId = Process::max('process_id') ?? 0;
                $nextId = $maxId + 1;
                $data['process_id'] = $nextId;
                
                // Generate code automatically if not provided
                if (empty($data['code'])) {
                    $data['code'] = 'PROC-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                }
            }

            // Extract process machines data
            $processMachinesData = $data['process_machines'] ?? [];
            unset($data['process_machines']);

            // Create the process
            $process = Process::create($data);

            // Create process machines if provided
            if (!empty($processMachinesData)) {
                foreach ($processMachinesData as $index => $machineData) {
                    $maxMachineId = ProcessMachine::max('process_machine_id') ?? 0;
                    $machineData['process_machine_id'] = $maxMachineId + 1;
                    $machineData['process_id'] = $process->process_id;
                    
                    // Ensure step_order is set
                    if (!isset($machineData['step_order'])) {
                        $machineData['step_order'] = $index + 1;
                    }
                    
                    // Extract variables data
                    $variablesData = $machineData['variables'] ?? [];
                    unset($machineData['variables']);
                    
                    // Create process machine
                    $processMachine = ProcessMachine::create($machineData);
                    
                    // Create process machine variables if provided
                    \Log::info('Variables data for machine ' . $processMachine->process_machine_id, ['variables' => $variablesData]);
                    
                    if (!empty($variablesData)) {
                        foreach ($variablesData as $variableData) {
                            $maxVariableId = \App\Models\ProcessMachineVariable::max('variable_id') ?? 0;
                            $variableData['variable_id'] = $maxVariableId + 1;
                            $variableData['process_machine_id'] = $processMachine->process_machine_id;
                            
                            \Log::info('Creating variable', ['data' => $variableData]);
                            $createdVar = \App\Models\ProcessMachineVariable::create($variableData);
                            \Log::info('Variable created', ['id' => $createdVar->variable_id]);
                        }
                    }
                }
            }

            DB::commit();

            // Load relationships for response
            $process->load(['processMachines.machine']);

            return response()->json(new ProcessResource($process), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create process: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Process $process): JsonResponse
    {
        // Eager load process machines with their associated machines
        $process->load(['processMachines.machine']);
        
        return response()->json(new ProcessResource($process));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProcessRequest $request, Process $process): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            $data = $request->validated();
            
            // Extract process machines data
            $processMachinesData = $data['process_machines'] ?? null;
            unset($data['process_machines']);

            // Update the process
            $process->update($data);

            // Handle process machines update if provided
            if ($processMachinesData !== null) {
                // Delete existing process machines
                ProcessMachine::where('process_id', $process->process_id)->delete();
                
                // Create new process machines
                foreach ($processMachinesData as $index => $machineData) {
                    $maxMachineId = ProcessMachine::max('process_machine_id') ?? 0;
                    $machineData['process_machine_id'] = $maxMachineId + 1;
                    $machineData['process_id'] = $process->process_id;
                    
                    if (!isset($machineData['step_order'])) {
                        $machineData['step_order'] = $index + 1;
                    }
                    
                    ProcessMachine::create($machineData);
                }
            }

            DB::commit();

            // Reload relationships
            $process->load(['processMachines.machine']);

            return response()->json(new ProcessResource($process));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update process: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete the specified resource.
     */
    public function destroy(Process $process): Response
    {
        try {
            DB::beginTransaction();
            
            // Delete associated process machines first
            ProcessMachine::where('process_id', $process->process_id)->delete();
            
            // Delete the process
            $process->delete();
            
            DB::commit();

            return response()->noContent();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete process: ' . $e->getMessage()], 500);
        }
    }
}