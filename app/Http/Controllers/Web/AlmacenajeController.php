<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Storage;
use App\Models\ProductionBatch;
use App\Models\CustomerOrder;
use App\Models\OrderEnvioTracking;
use App\Services\PlantaCrudsIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlmacenajeController extends Controller
{
    public function index()
    {
        // Mostrar TODOS los lotes certificados (incluyendo los ya almacenados)
        $lotes = ProductionBatch::whereHas('latestFinalEvaluation', function($query) {
                $query->whereRaw("LOWER(reason) NOT LIKE '%falló%'");
            })
            ->with([
                'order.customer', 
                'order.orderProducts.product.unit',
                'order.destinations.destinationProducts.orderProduct.product',
                'latestFinalEvaluation', 
                'storage'
            ])
            ->orderBy('creation_date', 'desc')
            ->get();
        
        // Preparar datos de pedidos para JavaScript
        $ordersData = [];
        foreach ($lotes as $lote) {
            if ($lote->order) {
                $ordersData[$lote->order_id] = [
                    'order_number' => $lote->order->order_number ?? 'N/A',
                    'destinations' => $lote->order->destinations->map(function($dest) {
                        return [
                            'address' => $dest->address ?? 'N/A',
                            'reference' => $dest->reference ?? '-',
                            'contact_name' => $dest->contact_name ?? '-',
                            'contact_phone' => $dest->contact_phone ?? '-',
                            'delivery_instructions' => $dest->delivery_instructions ?? '-',
                        ];
                    })->toArray()
                ];
            }
        }

        // Calcular estadísticas sobre TODOS los lotes, no solo los filtrados
        $allLotes = ProductionBatch::with(['latestFinalEvaluation', 'storage'])->get();
        
        // Lotes disponibles para almacenar (certificados sin almacenar)
        $lotesDisponibles = $allLotes->filter(function($lote) {
            $eval = $lote->latestFinalEvaluation;
            $esCertificado = $eval && !str_contains(strtolower($eval->reason ?? ''), 'falló');
            return $esCertificado && $lote->storage->isEmpty();
        });
        
        // Lotes certificados (todos los que tienen evaluación exitosa)
        $lotesCertificados = $allLotes->filter(function($lote) {
            $eval = $lote->latestFinalEvaluation;
            return $eval && !str_contains(strtolower($eval->reason ?? ''), 'falló');
        });
        
        // Lotes sin certificar (sin evaluación o evaluación fallida)
        $lotesSinCertificar = $allLotes->filter(function($lote) {
            $eval = $lote->latestFinalEvaluation;
            return !$eval || str_contains(strtolower($eval->reason ?? ''), 'falló');
        });
        
        // Lotes ya almacenados
        $lotesAlmacenados = $allLotes->filter(function($lote) {
            return $lote->storage->isNotEmpty();
        });

        $stats = [
            'disponibles' => $lotesDisponibles->count(),
            'certificados' => $lotesCertificados->count(),
            'sin_certificar' => $lotesSinCertificar->count(),
            'almacenados' => $lotesAlmacenados->count(),
        ];

        return view('almacenaje', compact('lotes', 'stats', 'ordersData'));
    }

    public function obtenerAlmacenajesPorLote($batchId)
    {
        $almacenajes = Storage::where('batch_id', $batchId)
            ->orderBy('storage_date', 'desc')
            ->get();

        return response()->json($almacenajes);
    }

    public function almacenar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => 'required|integer|exists:production_batch,batch_id',
            'condition' => 'required|string|max:100',
            'observations' => 'nullable|string|max:500',
            'pickup_latitude' => 'required|numeric|between:-90,90',
            'pickup_longitude' => 'required|numeric|between:-180,180',
            'pickup_address' => 'required|string|max:500',
            'pickup_reference' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $batch = ProductionBatch::with('storage')->findOrFail($request->batch_id);

            // Verificar que el lote no tenga almacenajes previos
            if ($batch->storage->isNotEmpty()) {
                return redirect()->back()
                    ->with('error', 'Este lote ya ha sido almacenado. Solo se permite almacenar una vez toda la cantidad.')
                    ->withInput();
            }

            // La cantidad se toma del lote (producida o objetivo)
            $producedQuantity = $batch->produced_quantity ?? 0;
            $targetQuantity = $batch->target_quantity ?? 0;
            $quantityToStore = ($producedQuantity > 0) ? $producedQuantity : $targetQuantity;

            // Sincronizar la secuencia con el máximo ID existente
            $maxStorageId = DB::table('storage')->max('storage_id');
            
            // Solo sincronizar la secuencia si hay registros existentes
            // Si no hay registros, PostgreSQL manejará automáticamente el siguiente valor
            if ($maxStorageId !== null && $maxStorageId > 0) {
                DB::statement("SELECT setval('storage_seq', {$maxStorageId}, true)");
            }

            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('storage_seq') as id")->id;

            $storage = Storage::create([
                'storage_id' => $nextId,
                'batch_id' => $request->batch_id,
                'location' => 'Almacén Principal', // Valor por defecto ya que no se usa
                'condition' => $request->condition,
                'quantity' => $quantityToStore,
                'observations' => $request->observations,
                'pickup_latitude' => $request->pickup_latitude,
                'pickup_longitude' => $request->pickup_longitude,
                'pickup_address' => $request->pickup_address,
                'pickup_reference' => $request->pickup_reference,
                'storage_date' => now(),
            ]);

            DB::commit();

            // Enviar pedido a plantaCruds para crear envío con ubicación de recojo
            try {
                $order = $batch->order;
                if ($order) {
                    $integration = new PlantaCrudsIntegrationService();
                    $results = $integration->sendOrderToShipping($order, $storage);

                    // Guardar tracking por cada resultado
                    foreach ($results as $res) {
                        OrderEnvioTracking::create([
                            'order_id' => $order->order_id,
                            'destination_id' => $res['destination_id'] ?? null,
                            'envio_id' => $res['envio_id'] ?? null,
                            'envio_codigo' => $res['envio_codigo'] ?? null,
                            'status' => $res['success'] ? 'success' : 'failed',
                            'error_message' => $res['success'] ? null : ($res['error'] ?? 'Unknown error'),
                            'request_data' => $res['response']['request'] ?? null,
                            'response_data' => $res['response'] ?? null,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error integrando con plantaCruds al almacenar lote: ' . $e->getMessage(), [
                    'batch_id' => $request->batch_id,
                    'storage_id' => $nextId
                ]);
                // No fallar el almacenamiento si falla la integración
            }

            return redirect()->route('almacenaje')
                ->with('success', 'Lote almacenado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al almacenar lote: ' . $e->getMessage())
                ->withInput();
        }
    }
}

