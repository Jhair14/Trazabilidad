<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductionBatch;
use App\Models\CustomerOrder;
use App\Models\RawMaterialBase;
use App\Models\Process;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GestionLotesController extends Controller
{
    public function index()
    {
        $lotes = ProductionBatch::with([
            'order.customer', 
            'rawMaterials.rawMaterial.materialBase',
            'rawMaterials.rawMaterial.supplier',
            'finalEvaluation'
        ])
            ->orderBy('creation_date', 'desc')
            ->paginate(15);

        // Estadísticas
        $stats = [
            'total' => ProductionBatch::count(),
            'pendientes' => ProductionBatch::whereNull('start_time')->count(),
            'en_proceso' => ProductionBatch::whereNotNull('start_time')
                ->whereNull('end_time')->count(),
            'completados' => ProductionBatch::whereNotNull('end_time')->count(),
            'certificados' => ProductionBatch::whereHas('finalEvaluation', function($query) {
                $query->whereRaw("LOWER(reason) NOT LIKE '%falló%'");
            })->count(),
        ];

        // Datos para formularios - pedidos con estado pendiente o en proceso
        $pedidos = CustomerOrder::where('priority', '>', 0)
            ->with('customer')
            ->orderBy('creation_date', 'desc')
            ->get();

        // Materias primas base activas (todas las guardadas)
        $materias_primas = RawMaterialBase::where('active', true)
            ->with('unit')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($mp) {
                // Calcular cantidad disponible dinámicamente desde las materias primas relacionadas
                $mp->calculated_available_quantity = $mp->rawMaterials()
                    ->where('receipt_conformity', true)
                    ->sum('available_quantity') ?? 0;
                return $mp;
            });

        // Preparar datos para JavaScript
        $materias_primas_json = $materias_primas->map(function($mp) {
            $available = $mp->calculated_available_quantity ?? ($mp->available_quantity ?? 0);
            return [
                'material_id' => $mp->material_id,
                'name' => $mp->name,
                'unit_code' => $mp->unit->code ?? 'N/A',
                'available' => number_format($available, 2)
            ];
        });

        $procesos = Process::where('active', true)->get();

        return view('gestion-lotes', compact('lotes', 'stats', 'pedidos', 'materias_primas', 'materias_primas_json', 'procesos'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'order_id' => 'nullable|integer|exists:customer_order,order_id',
            'target_quantity' => 'nullable|numeric|min:0',
            'raw_materials' => 'required|array|min:1',
            'raw_materials.*.material_id' => 'required|integer|exists:raw_material_base,material_id',
            'raw_materials.*.planned_quantity' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Validar que el pedido exista si se proporciona
            if ($request->order_id) {
                $order = CustomerOrder::find($request->order_id);
                if (!$order) {
                    throw new \Exception('El pedido especificado no existe');
                }
            }

            // Si no hay order_id, crear un pedido genérico o usar uno por defecto
            $orderId = $request->order_id;
            if (!$orderId) {
                // Sincronizar secuencia y obtener el siguiente ID para order
                $maxOrderId = DB::table('customer_order')->max('order_id');
                
                // Solo sincronizar la secuencia si hay registros existentes
                if ($maxOrderId !== null && $maxOrderId > 0) {
                    DB::statement("SELECT setval('customer_order_seq', {$maxOrderId}, true)");
                }
                
                // Obtener el siguiente ID de la secuencia
                $orderNextId = DB::selectOne("SELECT nextval('customer_order_seq') as id")->id;
                $orderNumber = 'INTERNO-' . str_pad($orderNextId, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd');
                
                // Crear un pedido genérico interno usando SQL directo
                $orderId = DB::selectOne("
                    INSERT INTO customer_order (order_id, order_number, customer_id, creation_date, priority, description)
                    VALUES (?, ?, ?, ?, ?, ?)
                    RETURNING order_id
                ", [
                    $orderNextId,
                    $orderNumber,
                    1, // Cliente por defecto
                    now()->toDateString(),
                    1,
                    'Pedido interno generado automáticamente'
                ])->order_id;
            }

            // Sincronizar secuencia y obtener el siguiente ID para batch
            $maxBatchId = DB::table('production_batch')->max('batch_id');
            
            // Solo sincronizar la secuencia si hay registros existentes
            if ($maxBatchId !== null && $maxBatchId > 0) {
                DB::statement("SELECT setval('production_batch_seq', {$maxBatchId}, true)");
            }
            
            // Obtener el siguiente ID de la secuencia
            $batchNextId = DB::selectOne("SELECT nextval('production_batch_seq') as id")->id;
            
            // Generar código de lote automáticamente
            $batchCode = 'LOTE-' . str_pad($batchNextId, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd');

            // Crear batch usando SQL directo
            $batchId = DB::selectOne("
                INSERT INTO production_batch (batch_id, order_id, batch_code, name, creation_date, target_quantity, observations)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                RETURNING batch_id
            ", [
                $batchNextId,
                $orderId,
                $batchCode,
                $request->name ?? 'Unnamed Batch',
                now()->toDateString(),
                $request->target_quantity,
                $request->observations
            ])->batch_id;
            
            $batch = ProductionBatch::find($batchId);

            // Crear batch raw materials - buscar o crear instancias de RawMaterial
            foreach ($request->raw_materials as $rm) {
                $materialBase = RawMaterialBase::with('rawMaterials')->findOrFail($rm['material_id']);
                
                // Calcular cantidad disponible dinámicamente desde las materias primas recibidas
                $calculatedAvailable = $materialBase->rawMaterials
                    ->where('receipt_conformity', true)
                    ->sum('available_quantity') ?? 0;
                
                // Si no hay materias primas recibidas, usar el valor almacenado
                if ($calculatedAvailable == 0 && $materialBase->rawMaterials->count() == 0) {
                    $calculatedAvailable = $materialBase->available_quantity ?? 0;
                }
                
                // Verificar disponibilidad
                if ($calculatedAvailable < $rm['planned_quantity']) {
                    throw new \Exception("No hay suficiente cantidad disponible de {$materialBase->name}. Disponible: {$calculatedAvailable}");
                }

                // Buscar una instancia de RawMaterial disponible para esta materia prima base
                $rawMaterial = \App\Models\RawMaterial::where('material_id', $rm['material_id'])
                    ->where('available_quantity', '>=', $rm['planned_quantity'])
                    ->orderBy('receipt_date', 'asc') // FIFO
                    ->first();

                if (!$rawMaterial) {
                    // Si no hay instancia disponible, crear una genérica o lanzar error
                    throw new \Exception("No hay materia prima recibida disponible para {$materialBase->name}. Debe recibir materia prima primero.");
                }

                // Sincronizar secuencia y crear batch raw material
                $maxBatchMaterialId = DB::table('batch_raw_material')->max('batch_material_id');
                
                // Solo sincronizar la secuencia si hay registros existentes
                if ($maxBatchMaterialId !== null && $maxBatchMaterialId > 0) {
                    DB::statement("SELECT setval('batch_raw_material_seq', {$maxBatchMaterialId}, true)");
                }
                
                // Obtener el siguiente ID de la secuencia
                $batchMaterialNextId = DB::selectOne("SELECT nextval('batch_raw_material_seq') as id")->id;
                
                $batchMaterialId = DB::selectOne("
                    INSERT INTO batch_raw_material (batch_material_id, batch_id, raw_material_id, planned_quantity, used_quantity)
                    VALUES (?, ?, ?, ?, ?)
                    RETURNING batch_material_id
                ", [
                    $batchMaterialNextId,
                    $batch->batch_id,
                    $rawMaterial->raw_material_id,
                    $rm['planned_quantity'],
                    0
                ])->batch_material_id;

                // Descontar de la materia prima base
                $materialBase->available_quantity -= $rm['planned_quantity'];
                $materialBase->save();

                // Descontar de la instancia de raw material
                $rawMaterial->available_quantity -= $rm['planned_quantity'];
                $rawMaterial->save();

                // Sincronizar secuencia y registrar en log de movimientos
                $maxLogId = DB::table('material_movement_log')->max('log_id');
                
                // Solo sincronizar la secuencia si hay registros existentes
                if ($maxLogId !== null && $maxLogId > 0) {
                    DB::statement("SELECT setval('material_movement_log_seq', {$maxLogId}, true)");
                }
                
                // Obtener el siguiente ID del log
                $logNextId = DB::selectOne("SELECT nextval('material_movement_log_seq') as id")->id;
                
                DB::selectOne("
                    INSERT INTO material_movement_log (log_id, material_id, movement_type_id, user_id, quantity, previous_balance, new_balance, description)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    RETURNING log_id
                ", [
                    $logNextId,
                    $rm['material_id'],
                    2, // Salida
                    auth()->id(),
                    $rm['planned_quantity'],
                    $materialBase->available_quantity + $rm['planned_quantity'],
                    $materialBase->available_quantity,
                    "Descuento por creación de lote (Código: {$batch->batch_code})"
                ]);
            }

            // Cambiar estado del pedido si existe
            if ($request->order_id) {
                // El estado se maneja por priority, no hay campo estado directo
                // Podrías agregar lógica aquí si necesitas cambiar el estado
            }

            DB::commit();

            return redirect()->route('gestion-lotes')
                ->with('success', 'Lote creado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al crear el lote: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $lote = ProductionBatch::with([
                'order.customer',
                'rawMaterials.rawMaterial.materialBase.unit',
                'rawMaterials.rawMaterial.supplier',
                'processMachineRecords.processMachine.machine',
                'finalEvaluation.inspector',
                'storage'
            ])->findOrFail($id);
            
            return response()->json([
                'batch_id' => $lote->batch_id,
                'batch_code' => $lote->batch_code,
                'name' => $lote->name,
                'order_id' => $lote->order_id,
                'order_number' => $lote->order->order_number ?? null,
                'order_name' => $lote->order->name ?? null,
                'customer_name' => $lote->order->customer->business_name ?? null,
                'creation_date' => $lote->creation_date,
                'start_time' => $lote->start_time,
                'end_time' => $lote->end_time,
                'target_quantity' => $lote->target_quantity,
                'produced_quantity' => $lote->produced_quantity,
                'observations' => $lote->observations,
                'raw_materials' => $lote->rawMaterials->map(function($rm) {
                    return [
                        'material_name' => $rm->rawMaterial->materialBase->name ?? 'N/A',
                        'unit' => $rm->rawMaterial->materialBase->unit->code ?? 'N/A',
                        'supplier' => $rm->rawMaterial->supplier->business_name ?? 'N/A',
                        'planned_quantity' => $rm->planned_quantity,
                        'used_quantity' => $rm->used_quantity,
                    ];
                }),
                'evaluation' => $lote->finalEvaluation->first() ? [
                    'reason' => $lote->finalEvaluation->first()->reason,
                    'observations' => $lote->finalEvaluation->first()->observations,
                    'evaluation_date' => $lote->finalEvaluation->first()->evaluation_date,
                ] : null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lote no encontrado'], 404);
        }
    }

    public function edit($id)
    {
        try {
            $lote = ProductionBatch::with([
                'order.customer',
                'rawMaterials.rawMaterial.materialBase.unit',
            ])->findOrFail($id);
            
            return response()->json([
                'batch_id' => $lote->batch_id,
                'name' => $lote->name,
                'order_id' => $lote->order_id,
                'target_quantity' => $lote->target_quantity,
                'observations' => $lote->observations,
                'raw_materials' => $lote->rawMaterials->map(function($rm) {
                    return [
                        'material_id' => $rm->rawMaterial->material_id,
                        'planned_quantity' => $rm->planned_quantity,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lote no encontrado'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'order_id' => 'nullable|integer|exists:customer_order,order_id',
            'target_quantity' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $lote = ProductionBatch::findOrFail($id);
            
            // Solo permitir editar si el lote no ha comenzado
            if ($lote->start_time) {
                throw new \Exception('No se puede editar un lote que ya ha comenzado su producción');
            }

            $lote->update([
                'name' => $request->name,
                'order_id' => $request->order_id ?? $lote->order_id,
                'target_quantity' => $request->target_quantity,
                'observations' => $request->observations,
            ]);

            DB::commit();

            return redirect()->route('gestion-lotes')
                ->with('success', 'Lote actualizado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar el lote: ' . $e->getMessage())
                ->withInput();
        }
    }
}

