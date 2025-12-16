<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use App\Http\Resources\RawMaterialResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RawMaterialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $materials = RawMaterial::with(['materialBase.unit', 'supplier'])
                ->orderBy('fecha_recepcion', 'desc')
                ->orderBy('materia_prima_id', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json(RawMaterialResource::collection($materials)->response()->getData());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener materias primas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $material = RawMaterial::with(['materialBase.unit', 'supplier'])->findOrFail($id);
            return response()->json(new RawMaterialResource($material));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Materia prima no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'material_id' => 'required|integer|exists:materia_prima_base,material_id',
            'supplier_id' => 'required|integer|exists:proveedor,proveedor_id',
            'supplier_batch' => 'nullable|string|max:100',
            'invoice_number' => 'nullable|string|max:100',
            'receipt_date' => 'required|date',
            'expiration_date' => 'nullable|date',
            'quantity' => 'required|numeric|min:0',
            'receipt_conformity' => 'nullable|boolean',
            'observations' => 'nullable|string|max:500',
            'solicitud_id' => 'nullable|integer|exists:solicitud_material,solicitud_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos incompletos o inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Get the next ID manually since sequence doesn't exist
            $maxId = RawMaterial::max('materia_prima_id') ?? 0;
            $nextId = $maxId + 1;
            
            // Convert conformity to boolean
            $receiptConformity = $request->receipt_conformity == '1' || $request->receipt_conformity === 1 || $request->receipt_conformity === true;

            // Get base material for previous balance
            $baseMaterial = \App\Models\RawMaterialBase::findOrFail($request->material_id);
            $previousBalance = $baseMaterial->cantidad_disponible ?? 0;

            $material = RawMaterial::create([
                'materia_prima_id' => $nextId,
                'material_id' => $request->material_id,
                'proveedor_id' => $request->supplier_id,
                'lote_proveedor' => $request->supplier_batch,
                'numero_factura' => $request->invoice_number,
                'fecha_recepcion' => $request->receipt_date,
                'fecha_vencimiento' => $request->expiration_date,
                'cantidad' => $request->quantity,
                'cantidad_disponible' => $request->quantity,
                'conformidad_recepcion' => $receiptConformity,
                'observaciones' => $request->observations,
            ]);

            // Update RawMaterialBase available quantity ONLY if conformity is true
            if ($receiptConformity) {
                $baseMaterial->increment('cantidad_disponible', $request->quantity);
            }

            // Link to request if provided
            if ($request->has('solicitud_id') && $request->solicitud_id) {
                \Illuminate\Support\Facades\Log::info('Processing request update', [
                    'solicitud_id' => $request->solicitud_id,
                    'material_id' => $request->material_id,
                    'quantity' => $request->quantity
                ]);

                $materialRequest = \App\Models\MaterialRequest::with('details')->find($request->solicitud_id);
                
                if ($materialRequest) {
                    $detail = $materialRequest->details->where('material_id', $request->material_id)->first();
                    
                    if ($detail) {
                        \Illuminate\Support\Facades\Log::info('Found detail to update', ['detail_id' => $detail->detalle_id]);
                        // Handle null value for increment
                        $currentAmount = $detail->cantidad_aprobada ?? 0;
                        $detail->cantidad_aprobada = $currentAmount + $request->quantity;
                        $detail->save();
                    } else {
                        \Illuminate\Support\Facades\Log::warning('Detail not found for material', ['material_id' => $request->material_id]);
                    }
                } else {
                    \Illuminate\Support\Facades\Log::warning('Request not found', ['solicitud_id' => $request->solicitud_id]);
                }
            }

            // Create movement log
            $maxLogId = DB::table('registro_movimiento_material')->max('registro_id');
            if ($maxLogId !== null && $maxLogId > 0) {
                DB::statement("SELECT setval('registro_movimiento_material_seq', {$maxLogId}, true)");
            }
            $logNextId = DB::selectOne("SELECT nextval('registro_movimiento_material_seq') as id")->id;
            
            DB::table('registro_movimiento_material')->insert([
                'registro_id' => $logNextId,
                'material_id' => $request->material_id,
                'tipo_movimiento_id' => 1, // Entrada
                'operador_id' => auth()->id() ?? 1, // Fallback to admin if no auth
                'cantidad' => $request->quantity,
                'saldo_anterior' => $previousBalance,
                'saldo_nuevo' => $receiptConformity ? ($previousBalance + $request->quantity) : $previousBalance,
                'descripcion' => 'Recepción de materia prima' . ($receiptConformity ? ' (Conforme)' : ' (No conforme)'),
                'fecha_movimiento' => now()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Materia prima creada exitosamente',
                'raw_material_id' => $material->raw_material_id
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear materia prima',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'supplier_batch' => 'nullable|string|max:100',
            'invoice_number' => 'nullable|string|max:100',
            'receipt_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'quantity' => 'nullable|numeric|min:0',
            'available_quantity' => 'nullable|numeric|min:0',
            'receipt_conformity' => 'nullable|boolean',
            'observations' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $material = RawMaterial::findOrFail($id);
            $material->update($request->only([
                'supplier_batch', 'invoice_number', 'receipt_date',
                'expiration_date', 'quantity', 'available_quantity',
                'receipt_conformity', 'observations'
            ]));

            return response()->json([
                'message' => 'Materia prima actualizada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar materia prima',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $material = RawMaterial::findOrFail($id);
            $material->delete();

            return response()->json([
                'message' => 'Materia prima eliminada satisfactoriamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar materia prima',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

