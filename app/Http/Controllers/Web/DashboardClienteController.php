<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardClienteController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Cargar la relación role si no está cargada
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        // Obtener pedidos del cliente actual (buscando por customer relacionado con el operador)
        $customerId = $user->customer_id ?? null;
        $customer = null;
        
        if (!$customerId) {
            // Buscar por email
            $customer = \App\Models\Customer::where('email', $user->email)->first();
            $customerId = $customer ? $customer->customer_id : null;
        }
        
        // Si no se encontró un cliente, crear uno automáticamente para este usuario
        if (!$customerId) {
            try {
                // Sincronizar secuencia de customer si es necesario
                $maxCustomerId = \App\Models\Customer::max('customer_id') ?? 0;
                try {
                    $seqResult = \Illuminate\Support\Facades\DB::selectOne("SELECT last_value FROM customer_seq");
                    $seqValue = $seqResult->last_value ?? 0;
                } catch (\Exception $e) {
                    $seqValue = 0;
                }
                
                if ($seqValue < $maxCustomerId) {
                    \Illuminate\Support\Facades\DB::statement("SELECT setval('customer_seq', $maxCustomerId, true)");
                }
                
                // Obtener el siguiente ID de la secuencia
                $nextId = \Illuminate\Support\Facades\DB::selectOne("SELECT nextval('customer_seq') as id")->id;
                
                // Crear un Customer automáticamente para este operador
                $customer = \App\Models\Customer::create([
                    'customer_id' => $nextId,
                    'business_name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'Cliente ' . $user->username,
                    'trading_name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'Cliente ' . $user->username,
                    'email' => $user->email ?? null,
                    'contact_person' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->username,
                    'active' => true,
                ]);
                
                $customerId = $customer->customer_id;
            } catch (\Exception $e) {
                // Si falla, usar el primer cliente activo como fallback
                $customer = \App\Models\Customer::where('active', true)->first();
                $customerId = $customer ? $customer->customer_id : null;
            }
        }
        
        // Si aún no hay customerId, mostrar pedidos vacíos
        if (!$customerId) {
            $pedidos = collect([]);
            $ultimoPedido = null;
        } else {
            $pedidos = CustomerOrder::where('customer_id', $customerId)
                ->with([
                    'batches.latestFinalEvaluation',
                    'batches.processMachineRecords.processMachine',
                    'batches.storage',
                    'materialRequests'
                ])
                ->orderBy('creation_date', 'desc')
                ->get();
            
            // Obtener el último pedido para seguimiento
            $ultimoPedido = $pedidos->first();
            
            // Si hay último pedido, cargar más información
            if ($ultimoPedido) {
                $ultimoPedido->load([
                    'batches.latestFinalEvaluation.inspector',
                    'batches.processMachineRecords.processMachine.machine',
                    'batches.processMachineRecords.processMachine.process',
                    'batches.processMachineRecords.operator',
                    'batches.storage',
                    'materialRequests.details.material'
                ]);
            }
        }

        // Calcular estadísticas reales
        $totalPedidos = $pedidos->count();
        $pedidosPendientes = $pedidos->filter(function($pedido) {
            // Pendiente si no tiene lotes o todos los lotes están pendientes
            if ($pedido->batches->isEmpty()) {
                return true;
            }
            // Si tiene lotes, verificar si alguno está en proceso
            return $pedido->batches->some(function($batch) {
                return !$batch->latestFinalEvaluation && $batch->processMachineRecords->isNotEmpty();
            });
        })->count();
        
        $pedidosCompletados = $pedidos->filter(function($pedido) {
            // Completado si tiene al menos un lote certificado
            return $pedido->batches->some(function($batch) {
                $eval = $batch->latestFinalEvaluation;
                return $eval && !str_contains(strtolower($eval->reason ?? ''), 'falló');
            });
        })->count();
        
        $pedidosEnProceso = $pedidos->filter(function($pedido) {
            // En proceso si tiene lotes con registros pero sin certificar
            return $pedido->batches->some(function($batch) {
                return $batch->processMachineRecords->isNotEmpty() && !$batch->latestFinalEvaluation;
            });
        })->count();

        $stats = [
            'total_pedidos' => $totalPedidos,
            'pedidos_pendientes' => $pedidosPendientes,
            'pedidos_completados' => $pedidosCompletados,
            'pedidos_en_proceso' => $pedidosEnProceso,
        ];

        return view('dashboard-cliente', compact('pedidos', 'stats', 'ultimoPedido', 'customer'));
    }

    public function obtenerDetallesPedido($orderId)
    {
        $user = Auth::user();
        
        // Buscar customer del usuario
        $customerId = $user->customer_id ?? null;
        if (!$customerId) {
            $customer = \App\Models\Customer::where('email', $user->email)->first();
            $customerId = $customer ? $customer->customer_id : null;
        }
        
        // Obtener el pedido solo si pertenece al cliente
        $pedido = CustomerOrder::where('order_id', $orderId)
            ->where('customer_id', $customerId)
            ->with([
                'batches.latestFinalEvaluation.inspector',
                'batches.processMachineRecords.processMachine.machine',
                'batches.processMachineRecords.processMachine.process',
                'batches.processMachineRecords.operator',
                'batches.storage',
                'batches.rawMaterials.rawMaterial.materialBase',
                'materialRequests.details.material'
            ])
            ->first();
        
        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado o no autorizado'], 404);
        }
        
        return response()->json([
            'pedido' => [
                'order_id' => $pedido->order_id,
                'order_number' => $pedido->order_number,
                'description' => $pedido->description,
                'creation_date' => $pedido->creation_date->format('d/m/Y'),
                'delivery_date' => $pedido->delivery_date ? $pedido->delivery_date->format('d/m/Y') : null,
                'priority' => $pedido->priority,
                'observations' => $pedido->observations,
            ],
            'lotes' => $pedido->batches->map(function($batch) {
                $eval = $batch->latestFinalEvaluation;
                return [
                    'batch_id' => $batch->batch_id,
                    'batch_code' => $batch->batch_code,
                    'name' => $batch->name,
                    'creation_date' => $batch->creation_date->format('d/m/Y'),
                    'start_time' => $batch->start_time ? $batch->start_time->format('d/m/Y H:i') : null,
                    'end_time' => $batch->end_time ? $batch->end_time->format('d/m/Y H:i') : null,
                    'target_quantity' => $batch->target_quantity,
                    'produced_quantity' => $batch->produced_quantity,
                    'estado' => $eval 
                        ? (str_contains(strtolower($eval->reason ?? ''), 'falló') ? 'No Certificado' : 'Certificado')
                        : ($batch->processMachineRecords->isNotEmpty() ? 'En Proceso' : 'Pendiente'),
                    'certificacion' => $eval ? [
                        'evaluation_date' => $eval->evaluation_date->format('d/m/Y H:i'),
                        'reason' => $eval->reason,
                        'inspector' => $eval->inspector ? $eval->inspector->first_name . ' ' . $eval->inspector->last_name : 'N/A',
                    ] : null,
                    'maquinas' => $batch->processMachineRecords->map(function($record) {
                        return [
                            'nombre' => $record->processMachine->name ?? 'N/A',
                            'maquina' => $record->processMachine->machine->name ?? 'N/A',
                            'cumple_estandar' => $record->meets_standard,
                            'fecha' => $record->record_date ? $record->record_date->format('d/m/Y H:i') : null,
                        ];
                    }),
                    'almacenamiento' => $batch->storage->map(function($st) {
                        return [
                            'location' => $st->location,
                            'condition' => $st->condition,
                            'quantity' => $st->quantity,
                            'storage_date' => $st->storage_date->format('d/m/Y H:i'),
                        ];
                    }),
                ];
            }),
            'solicitudes_materia_prima' => $pedido->materialRequests->map(function($req) {
                return [
                    'request_number' => $req->request_number,
                    'request_date' => $req->request_date->format('d/m/Y'),
                    'required_date' => $req->required_date->format('d/m/Y'),
                    'estado' => $req->priority == 0 ? 'Completada' : 'Pendiente',
                    'materiales' => $req->details->map(function($det) {
                        return [
                            'material' => $det->material->name,
                            'cantidad_solicitada' => $det->requested_quantity,
                            'cantidad_aprobada' => $det->approved_quantity ?? 0,
                        ];
                    }),
                ];
            }),
        ]);
    }
}
