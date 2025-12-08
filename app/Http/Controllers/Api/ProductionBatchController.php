<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductionBatch;
use App\Models\BatchRawMaterial;
use App\Models\CustomerOrder;
use Illuminate\Http\Request;
use App\Http\Requests\ProductionBatchRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ProductionBatchResource;

class ProductionBatchController extends Controller
{
    /**
     * List all production batches
     */
    public function index(Request $request)
    {
        $batches = ProductionBatch::with([
            'order.customer', 
            'rawMaterials.rawMaterial.materialBase',
            'finalEvaluation'
        ])
            ->orderBy('creation_date', 'desc')
            ->orderBy('batch_id', 'desc')
            ->paginate($request->get('per_page', 15));

        return ProductionBatchResource::collection($batches);
    }

    /**
     * Get batch by ID
     */
    public function show(ProductionBatch $productionBatch): JsonResponse
    {
        // Only load relationships for tables that exist
        $productionBatch->load([
            'order.customer',
            'rawMaterials.rawMaterial.materialBase',
            'finalEvaluation',
            // Commented out until these tables are created:
            // 'processMachineRecords.processMachine.machine',
            // 'finalEvaluation.inspector',
            // 'storage'
        ]);

        return response()->json(new ProductionBatchResource($productionBatch));
    }

    /**
     * Create a new production batch
     */
    public function store(ProductionBatchRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:customer_order,order_id',
            'name' => 'nullable|string|max:100',
            'target_quantity' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string|max:500',
            'raw_materials' => 'nullable|array',
            'raw_materials.*.raw_material_id' => 'required|integer|exists:raw_material,raw_material_id',
            'raw_materials.*.planned_quantity' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos incompletos o inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Get the next ID manually since sequence doesn't exist
            $maxId = ProductionBatch::max('batch_id') ?? 0;
            $nextId = $maxId + 1;
            
            // Generar código de lote automáticamente
            $batchCode = 'LOTE-' . str_pad($nextId, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd');
            
            $batch = ProductionBatch::create([
                'batch_id' => $nextId,
                'order_id' => $request->order_id,
                'batch_code' => $batchCode,
                'name' => $request->name ?? 'Unnamed Batch',
                'creation_date' => now()->toDateString(),
                'target_quantity' => $request->target_quantity,
                'observations' => $request->observations,
            ]);

            // Create batch raw materials
            if ($request->has('raw_materials')) {
                foreach ($request->raw_materials as $rm) {
                    // Get the next ID manually
                    $maxBatchMaterialId = BatchRawMaterial::max('batch_material_id') ?? 0;
                    $batchMaterialId = $maxBatchMaterialId + 1;
                    
                    BatchRawMaterial::create([
                        'batch_material_id' => $batchMaterialId,
                        'batch_id' => $batch->batch_id,
                        'raw_material_id' => $rm['raw_material_id'],
                        'planned_quantity' => $rm['planned_quantity'],
                        'used_quantity' => 0,
                    ]);
                }
            }

            // Update order status
            $order = CustomerOrder::find($request->order_id);
            if ($order) {
                // You might want to update order status here
            }

            DB::commit();

            return response()->json([
                'message' => 'Lote de producción creado exitosamente',
                'batch_id' => $batch->batch_id
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear lote de producción',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update production batch
     */
    public function update(ProductionBatchRequest $request, ProductionBatch $productionBatch): JsonResponse
    {
        $productionBatch->update($request->validated());

        return response()->json(new ProductionBatchResource($productionBatch));
    }

    /**
     * Delete production batch
     */
    public function destroy(ProductionBatch $productionBatch): Response
    {
        // Check if batch can be deleted (not in use)
        // Commented out until process_machine_record table is created:
        // if ($productionBatch->processMachineRecords()->count() > 0) {
        //     return response()->json([
        //         'message' => 'No se puede eliminar un lote que tiene registros de proceso'
        //     ], 400);
        // }

        $productionBatch->delete();

        return response()->noContent();
    }

    /**
     * Get batches pending certification
     */
    public function getPendingCertification(Request $request): JsonResponse
    {
        $batches = ProductionBatch::with([
            'order.customer',
            'processMachineRecords.processMachine.process',
           'finalEvaluation'
        ])
            ->orderBy('creation_date', 'desc')
            ->get();

        return response()->json(ProductionBatchResource::collection($batches));
    }

    /**
     * Assign a process to a batch
     */
    public function assignProcess(Request $request, $batchId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'process_id' => 'required|integer|exists:process,process_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $batch = ProductionBatch::findOrFail($batchId);
            
            // Verify no records from another process exist
            $existingRecords = \App\Models\ProcessMachineRecord::where('batch_id', $batchId)
                ->with('processMachine')
                ->get();
            
            if ($existingRecords->isNotEmpty()) {
                $existingProcessIds = $existingRecords->pluck('processMachine.process_id')->unique()->filter();
                if ($existingProcessIds->isNotEmpty() && !$existingProcessIds->contains($request->process_id)) {
                    return response()->json([
                        'message' => 'Este lote ya tiene registros de otro proceso'
                    ], 400);
                }
            }
            
            // Verify process has machines
            $processMachines = \App\Models\ProcessMachine::with([
                'machine',
                'variables.standardVariable',
                'process'
            ])
                ->where('process_id', $request->process_id)
                ->orderBy('step_order')
                ->get();
                
            if ($processMachines->isEmpty()) {
                return response()->json([
                    'message' => 'El proceso seleccionado no tiene máquinas configuradas'
                ], 400);
            }
            
            // Return success with process machines
            return response()->json([
                'message' => 'Proceso asignado exitosamente',
                'process_id' => $request->process_id,
                'process_machines' => $processMachines,
                'completed_records' => []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al asignar proceso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get process machines for a batch
     */
    public function getProcessMachines(Request $request, $batchId): JsonResponse
    {
        try {
            $batch = ProductionBatch::with([
                'processMachineRecords.processMachine.process'
            ])->findOrFail($batchId);

            // Get process_id from query parameter, existing records, or return empty
            $processId = $request->query('process_id');
            
            if (!$processId && $batch->processMachineRecords->isNotEmpty()) {
                $firstRecord = $batch->processMachineRecords->first();
                if ($firstRecord->processMachine) {
                    $processId = $firstRecord->processMachine->process_id;
                }
            }

            if (!$processId) {
                return response()->json([
                    'process_machines' => [],
                    'completed_records' => []
                ]);
            }

            // Get all machines for the process
            $processMachines = \App\Models\ProcessMachine::with([
                'machine',
                'variables.standardVariable',
                'process'
            ])
                ->where('process_id', $processId)
                ->orderBy('step_order')
                ->get();

            // Get completed records
            $completedRecords = $batch->processMachineRecords->pluck('process_machine_id')->toArray();

            return response()->json([
                'process_machines' => $processMachines,
                'completed_records' => $completedRecords,
                'process_id' => $processId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener máquinas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalize batch certification
     */
    public function finalizeCertification(Request $request, $batchId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'observations' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();
        try {
            $batch = ProductionBatch::findOrFail($batchId);

            // Get process records
            $records = \App\Models\ProcessMachineRecord::where('batch_id', $batchId)
                ->with('processMachine.process')
                ->get();

            if ($records->isEmpty()) {
                return response()->json([
                    'message' => 'El lote no tiene registros de proceso'
                ], 400);
            }

            // Get process_id
            $firstRecord = $records->first();
            if (!$firstRecord->processMachine || !$firstRecord->processMachine->process_id) {
                return response()->json([
                    'message' => 'No se pudo identificar el proceso del lote'
                ], 400);
            }

            $processId = $firstRecord->processMachine->process_id;

            // Verify all records are from same process
            $processIds = $records->pluck('processMachine.process_id')->unique()->filter();
            if ($processIds->count() > 1) {
                return response()->json([
                    'message' => 'El lote tiene registros de múltiples procesos'
                ], 400);
            }

            // Get expected machine count
            $processMachines = \App\Models\ProcessMachine::where('process_id', $processId)
                ->orderBy('step_order')
                ->get();
            
            $expectedCount = $processMachines->count();
            $actualCount = $records->count();

            if ($actualCount < $expectedCount) {
                return response()->json([
                    'message' => "Faltan formularios. Solo hay {$actualCount} de {$expectedCount} máquinas"
                ], 400);
            }

            // Evaluate if any machine failed
            $failed = $records->firstWhere('meets_standard', false);
            $status = $failed ? 'No Certificado' : 'Certificado';
            
            $machineName = 'N/A';
            if ($failed && $failed->processMachine) {
                $machineName = $failed->processMachine->name;
            }
            
            $reason = $failed 
                ? "Falló en la máquina {$machineName}"
                : 'Todas las máquinas cumplen los valores estándar';

            // Save final evaluation
            $existingEvaluation = \App\Models\ProcessFinalEvaluation::where('batch_id', $batchId)->first();
            
            if ($existingEvaluation) {
                $existingEvaluation->update([
                    'inspector_id' => auth()->id(),
                    'reason' => $reason,
                    'observations' => $request->observations,
                    'evaluation_date' => now(),
                ]);
            } else {
                $maxId = \App\Models\ProcessFinalEvaluation::max('evaluation_id') ?? 0;
                $nextId = $maxId + 1;
                
                \App\Models\ProcessFinalEvaluation::create([
                    'evaluation_id' => $nextId,
                    'batch_id' => $batchId,
                    'inspector_id' => auth()->id(),
                    'reason' => $reason,
                    'observations' => $request->observations,
                    'evaluation_date' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => $status . ' - El proceso ha sido finalizado',
                'status' => $status,
                'reason' => $reason
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al finalizar proceso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get certification log for a batch
     */
    public function getCertificationLog($batchId): JsonResponse
    {
        try {
            $batch = ProductionBatch::with([
                'processMachineRecords.processMachine.machine',
                'processMachineRecords.processMachine.process',
                'finalEvaluation.inspector'
            ])->findOrFail($batchId);

            // Get the final evaluation (it's a collection, so get first)
            $finalEvaluation = $batch->finalEvaluation->first();
            
            if (!$finalEvaluation) {
                return response()->json([
                    'message' => 'El lote aún no ha sido evaluado'
                ], 404);
            }

            // Get records ordered by step_order
            $records = $batch->processMachineRecords->sortBy(function($record) {
                return $record->processMachine ? $record->processMachine->step_order : 999;
            })->values();

            // Format machines
            $machines = $records->map(function($record) {
                return [
                    'step_number' => $record->processMachine ? $record->processMachine->step_order : null,
                    'machine_name' => $record->processMachine ? $record->processMachine->name : 'N/A',
                    'entered_variables' => $record->entered_variables ?? [],
                    'meets_standard' => $record->meets_standard ?? false,
                    'record_date' => $record->record_date ? $record->record_date->toDateTimeString() : null,
                ];
            });

            // Format final result
            $finalResult = [
                'status' => str_contains(strtolower($finalEvaluation->reason ?? ''), 'falló') 
                    ? 'No Certificado' 
                    : 'Certificado',
                'reason' => $finalEvaluation->reason ?? 'N/A',
                'evaluation_date' => $finalEvaluation->evaluation_date 
                    ? $finalEvaluation->evaluation_date->toDateTimeString() 
                    : null,
                'inspector' => $finalEvaluation->inspector 
                    ? $finalEvaluation->inspector->name 
                    : 'N/A',
            ];

            return response()->json([
                'machines' => $machines,
                'final_result' => $finalResult
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener log: ' . $e->getMessage()
            ], 500);
        }
    }
}

