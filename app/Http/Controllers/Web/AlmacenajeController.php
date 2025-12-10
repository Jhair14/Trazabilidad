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
                $query->whereRaw("LOWER(razon) NOT LIKE '%falló%'");
            })
            ->with([
                'order.customer', 
                'order.orderProducts.product.unit',
                'order.destinations.destinationProducts.orderProduct.product',
                'latestFinalEvaluation', 
                'storage'
            ])
            ->orderBy('fecha_creacion', 'desc')
            ->get();
        
        // Preparar datos de pedidos para JavaScript
        $ordersData = [];
        foreach ($lotes as $lote) {
            if ($lote->order) {
                $ordersData[$lote->pedido_id] = [
                    'numero_pedido' => $lote->order->numero_pedido ?? 'N/A',
                    'destinations' => $lote->order->destinations->map(function($dest) {
                        return [
                            'address' => $dest->direccion ?? 'N/A',
                            'reference' => $dest->referencia ?? '-',
                            'contact_name' => $dest->nombre_contacto ?? '-',
                            'contact_phone' => $dest->telefono_contacto ?? '-',
                            'delivery_instructions' => $dest->instrucciones_entrega ?? '-',
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
            $esCertificado = $eval && !str_contains(strtolower($eval->razon ?? ''), 'falló');
            return $esCertificado && $lote->storage->isEmpty();
        });
        
        // Lotes certificados (todos los que tienen evaluación exitosa)
        $lotesCertificados = $allLotes->filter(function($lote) {
            $eval = $lote->latestFinalEvaluation;
            return $eval && !str_contains(strtolower($eval->razon ?? ''), 'falló');
        });
        
        // Lotes sin certificar (sin evaluación o evaluación fallida)
        $lotesSinCertificar = $allLotes->filter(function($lote) {
            $eval = $lote->latestFinalEvaluation;
            return !$eval || str_contains(strtolower($eval->razon ?? ''), 'falló');
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
        $almacenajes = Storage::where('lote_id', $batchId)
            ->orderBy('fecha_almacenaje', 'desc')
            ->get();

        return response()->json($almacenajes);
    }

    public function almacenar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lote_id' => 'required|integer|exists:lote_produccion,lote_id',
            'condicion' => 'required|string|max:100',
            'observaciones' => 'nullable|string|max:500',
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
            $batch = ProductionBatch::with('storage')->findOrFail($request->lote_id);

            // Verificar que el lote no tenga almacenajes previos
            if ($batch->storage->isNotEmpty()) {
                return redirect()->back()
                    ->with('error', 'Este lote ya ha sido almacenado. Solo se permite almacenar una vez toda la cantidad.')
                    ->withInput();
            }

            // La cantidad se toma del lote (producida o objetivo)
            $producedQuantity = $batch->cantidad_producida ?? 0;
            $targetQuantity = $batch->cantidad_objetivo ?? 0;
            $quantityToStore = ($producedQuantity > 0) ? $producedQuantity : $targetQuantity;

            // Sincronizar la secuencia con el máximo ID existente
            $maxStorageId = DB::table('almacenaje')->max('almacenaje_id');
            
            // Solo sincronizar la secuencia si hay registros existentes
            // Si no hay registros, PostgreSQL manejará automáticamente el siguiente valor
            if ($maxStorageId !== null && $maxStorageId > 0) {
                DB::statement("SELECT setval('almacenaje_seq', {$maxStorageId}, true)");
            }

            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('almacenaje_seq') as id")->id;

            $storage = Storage::create([
                'almacenaje_id' => $nextId,
                'lote_id' => $request->lote_id,
                'ubicacion' => 'Almacén Principal', // Valor por defecto ya que no se usa
                'condicion' => $request->condicion,
                'cantidad' => $quantityToStore,
                'observaciones' => $request->observaciones,
                'latitud_recojo' => $request->pickup_latitude,
                'longitud_recojo' => $request->pickup_longitude,
                'direccion_recojo' => $request->pickup_address,
                'referencia_recojo' => $request->pickup_reference,
                'fecha_almacenaje' => now(),
            ]);

            DB::commit();

            // Enviar pedido a plantaCruds para crear envío con ubicación de recojo
            try {
                $order = ProductionBatch::with([
                    'order.customer',
                    'order.orderProducts.product.unit',
                    'order.destinations.destinationProducts.orderProduct.product'
                ])->findOrFail($request->lote_id)->order;
                
                if ($order && $order->destinations && $order->destinations->count() > 0) {
                    Log::info('Enviando pedido a PlantaCruds para crear envíos', [
                        'pedido_id' => $order->pedido_id,
                        'numero_pedido' => $order->numero_pedido,
                        'destinos_count' => $order->destinations->count(),
                        'storage_id' => $storage->almacenaje_id,
                    ]);
                    
                    $integration = new PlantaCrudsIntegrationService();
                    $results = $integration->sendOrderToShipping($order, $storage);

                    // Guardar tracking por cada resultado
                    foreach ($results as $res) {
                        OrderEnvioTracking::create([
                            'pedido_id' => $order->pedido_id,
                            'destino_id' => $res['destination_id'] ?? null,
                            'envio_id' => $res['envio_id'] ?? null,
                            'codigo_envio' => $res['envio_codigo'] ?? null,
                            'estado' => $res['success'] ? 'success' : 'failed',
                            'mensaje_error' => $res['success'] ? null : ($res['error'] ?? 'Unknown error'),
                            'datos_solicitud' => $res['response']['request'] ?? null,
                            'datos_respuesta' => $res['response'] ?? null,
                        ]);
                    }
                    
                    Log::info('Integración con PlantaCruds completada', [
                        'pedido_id' => $order->pedido_id,
                        'results_count' => count($results),
                        'successful' => collect($results)->where('success', true)->count(),
                    ]);
                } else {
                    Log::warning('No se pudo enviar pedido a PlantaCruds: pedido sin destinos', [
                        'lote_id' => $request->lote_id,
                        'pedido_id' => $order->pedido_id ?? null,
                        'has_order' => $order !== null,
                        'has_destinations' => $order && $order->destinations ? $order->destinations->count() : 0,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error integrando con plantaCruds al almacenar lote: ' . $e->getMessage(), [
                    'lote_id' => $request->lote_id,
                    'almacenaje_id' => $nextId,
                    'trace' => $e->getTraceAsString(),
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

