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
        $batches = ProductionBatch::with(['order.customer', 'rawMaterials.rawMaterial.materialBase'])
            ->orderBy('creation_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return ProductionBatchResource::collection($batches);
    }

    /**
     * Get batch by ID
     */
    public function show(ProductionBatch $productionBatch): JsonResponse
    {
        $productionBatch->load([
            'order.customer',
            'rawMaterials.rawMaterial.materialBase',
            'processMachineRecords.processMachine.machine',
            'finalEvaluation.inspector',
            'storage'
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
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('production_batch_seq') as id")->id;
            
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
                    // Obtener el siguiente ID de la secuencia
                    $batchMaterialId = DB::selectOne("SELECT nextval('batch_raw_material_seq') as id")->id;
                    
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

            return response()->json(new ProductionBatchResource($batch), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear el lote',
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
        if ($productionBatch->processMachineRecords()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar un lote que tiene registros de proceso'
            ], 400);
        }

        $productionBatch->delete();

        return response()->noContent();
    }
}

