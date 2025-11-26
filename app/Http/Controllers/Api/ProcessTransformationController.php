<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionBatch;
use App\Models\ProcessMachine;
use App\Models\ProcessMachineRecord;
use App\Models\ProcessMachineVariable;
use App\Models\Operator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProcessTransformationController extends Controller
{
    /**
     * Register transformation form for a machine in a batch
     */
    public function registerForm(Request $request, $batchId, $processMachineId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'entered_variables' => 'required|array',
            'observations' => 'nullable|string|max:500',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos incompletos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $operator = auth()->user();
            if (!$operator) {
                return response()->json([
                    'message' => 'No autenticado'
                ], 401);
            }

            $batch = ProductionBatch::findOrFail($batchId);
            $processMachine = ProcessMachine::with('variables.standardVariable')
                ->findOrFail($processMachineId);

            // Verify operator has access to this machine
            $hasAccess = $operator->machines()
                ->where('machine_id', $processMachine->machine_id)
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'message' => 'No tienes permiso para registrar variables de esta máquina'
                ], 403);
            }

            // Validate variables against standards
            $enteredVariables = $request->entered_variables;
            $meetsStandard = true;
            $errors = [];

            foreach ($processMachine->variables as $variable) {
                $varName = $variable->standardVariable->code ?? $variable->standardVariable->name;
                $enteredValue = $enteredVariables[$varName] ?? null;

                if ($variable->mandatory && $enteredValue === null) {
                    $meetsStandard = false;
                    $errors[] = "Variable {$varName} es obligatoria";
                    continue;
                }

                if ($enteredValue !== null) {
                    if ($enteredValue < $variable->min_value || $enteredValue > $variable->max_value) {
                        $meetsStandard = false;
                        $errors[] = "Variable {$varName} fuera de rango ({$variable->min_value} - {$variable->max_value})";
                    }
                }
            }

            // Verificar si ya existe un registro
            $existingRecord = ProcessMachineRecord::where('batch_id', $batchId)
                ->where('process_machine_id', $processMachineId)
                ->first();
            
            if ($existingRecord) {
                // Actualizar registro existente
                $existingRecord->update([
                    'operator_id' => $operator->operator_id,
                    'entered_variables' => json_encode($enteredVariables),
                    'meets_standard' => $meetsStandard,
                    'observations' => $request->observations,
                    'start_time' => $request->start_time ?? now(),
                    'end_time' => $request->end_time ?? now(),
                    'record_date' => now(),
                ]);
                $record = $existingRecord;
            } else {
                // Obtener el siguiente ID de la secuencia
                $nextId = DB::selectOne("SELECT nextval('process_machine_record_seq') as id")->id;
                
                // Crear nuevo registro
                $record = ProcessMachineRecord::create([
                    'record_id' => $nextId,
                    'batch_id' => $batchId,
                    'process_machine_id' => $processMachineId,
                    'operator_id' => $operator->operator_id,
                    'entered_variables' => json_encode($enteredVariables),
                    'meets_standard' => $meetsStandard,
                    'observations' => $request->observations,
                    'start_time' => $request->start_time ?? now(),
                    'end_time' => $request->end_time ?? now(),
                    'record_date' => now(),
                ]);
            }

            return response()->json([
                'message' => 'Proceso Completado',
                'cumple' => $meetsStandard,
                'errors' => $errors,
                'record_id' => $record->record_id
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transformation form for a machine in a batch
     */
    public function getForm($batchId, $processMachineId): JsonResponse
    {
        try {
            $record = ProcessMachineRecord::with(['processMachine.machine', 'operator'])
                ->where('batch_id', $batchId)
                ->where('process_machine_id', $processMachineId)
                ->first();

            if (!$record) {
                return response()->json([
                    'message' => 'Formulario no encontrado'
                ], 404);
            }

            return response()->json($record);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get process machines for a batch
     */
    public function getBatchProcess($batchId): JsonResponse
    {
        try {
            $batch = ProductionBatch::with('order')->findOrFail($batchId);
            
            // Get process machines for this batch
            // First, try to get from records, otherwise get from process linked to batch's order
            $processMachines = ProcessMachine::with(['machine', 'variables.standardVariable'])
                ->whereHas('records', function($query) use ($batchId) {
                    $query->where('batch_id', $batchId);
                })
                ->orderBy('step_order')
                ->get();

            // If no records, try to get from process (you may need to link process to batch/order)
            if ($processMachines->isEmpty()) {
                // This would require a process_id field in production_batch or linking through order
                // For now, return empty or handle based on your business logic
            }

            return response()->json($processMachines);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

