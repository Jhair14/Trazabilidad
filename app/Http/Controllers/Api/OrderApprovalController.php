<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\OrderProduct;
use App\Models\OrderEnvioTracking;
use App\Services\PlantaCrudsIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderApprovalController extends Controller
{
    /**
     * Obtener pedidos pendientes de aprobación
     */
    public function pendingOrders(Request $request): JsonResponse
    {
        try {
            $orders = CustomerOrder::with([
                'customer',
                'orderProducts.product.unit',
                'orderProducts' => function($query) {
                    $query->where('status', 'pendiente');
                }
            ])
            ->where('status', 'pendiente')
            ->orderBy('creation_date', 'desc')
            ->paginate($request->get('per_page', 15));

            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener pedidos pendientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles de un pedido para aprobación
     */
    public function show($id): JsonResponse
    {
        try {
            $order = CustomerOrder::with([
                'customer',
                'orderProducts.product.unit',
                'destinations.destinationProducts.orderProduct.product',
                'approver'
            ])->findOrFail($id);

            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Pedido no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Aprobar un producto específico del pedido
     */
    public function approveProduct(Request $request, $orderId, $productId): JsonResponse
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

        try {
            DB::beginTransaction();

            $orderProduct = OrderProduct::where('order_id', $orderId)
                ->where('order_product_id', $productId)
                ->where('status', 'pendiente')
                ->firstOrFail();

            $orderProduct->update([
                'status' => 'aprobado',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'observations' => $request->observations,
            ]);

            // Verificar si todos los productos están aprobados
            $pendingProducts = OrderProduct::where('order_id', $orderId)
                ->where('status', 'pendiente')
                ->count();

            if ($pendingProducts === 0) {
                // Todos los productos están aprobados, aprobar el pedido completo
                $order = CustomerOrder::findOrFail($orderId);
                $order->update([
                    'status' => 'aprobado',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Producto aprobado exitosamente',
                'order_product' => $orderProduct->load('product', 'approver')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al aprobar producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechazar un producto específico del pedido
     */
    public function rejectProduct(Request $request, $orderId, $productId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'La razón de rechazo es requerida',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $orderProduct = OrderProduct::where('order_id', $orderId)
                ->where('order_product_id', $productId)
                ->where('status', 'pendiente')
                ->firstOrFail();

            $orderProduct->update([
                'status' => 'rechazado',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            // Si se rechaza un producto, el pedido puede seguir pendiente con otros productos
            // o puede ser rechazado completamente según la lógica de negocio

            DB::commit();

            return response()->json([
                'message' => 'Producto rechazado exitosamente',
                'order_product' => $orderProduct->load('product', 'approver')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al rechazar producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprobar todo el pedido (todos los productos pendientes)
     */
    public function approveOrder(Request $request, $orderId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $order = CustomerOrder::with('orderProducts')
                ->where('status', 'pendiente')
                ->findOrFail($orderId);

            $pendingProducts = $order->orderProducts()
                ->where('status', 'pendiente')
                ->get();

            if ($pendingProducts->isEmpty()) {
                return response()->json([
                    'message' => 'No hay productos pendientes para aprobar'
                ], 400);
            }

            // Aprobar todos los productos pendientes
            OrderProduct::where('order_id', $orderId)
                ->where('status', 'pendiente')
                ->update([
                    'status' => 'aprobado',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);

            // Aprobar el pedido completo
            $order->update([
                'status' => 'aprobado',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            // Integración con plantaCruds - Enviar pedido aprobado
            $enviosCreated = [];
            $integrationErrors = [];
            
            try {
                $integrationService = new PlantaCrudsIntegrationService();
                $results = $integrationService->sendOrderToShipping($order);
                
                // Guardar tracking de cada destino
                foreach ($results as $result) {
                    $trackingData = [
                        'order_id' => $order->order_id,
                        'destination_id' => $result['destination_id'],
                        'status' => $result['success'] ? 'success' : 'failed',
                    ];
                    
                    if ($result['success']) {
                        $trackingData['envio_id'] = $result['envio_id'];
                        $trackingData['envio_codigo'] = $result['envio_codigo'];
                        $trackingData['response_data'] = $result['response'] ?? null;
                        $enviosCreated[] = [
                            'destination_id' => $result['destination_id'],
                            'envio_codigo' => $result['envio_codigo'],
                        ];
                    } else {
                        $trackingData['error_message'] = $result['error'];
                        $integrationErrors[] = [
                            'destination_id' => $result['destination_id'],
                            'error' => $result['error'],
                        ];
                    }
                    
                    OrderEnvioTracking::create($trackingData);
                }
                
                Log::info('PlantaCruds integration completed', [
                    'order_id' => $order->order_id,
                    'order_number' => $order->order_number,
                    'total_destinations' => count($results),
                    'successful' => count($enviosCreated),
                    'failed' => count($integrationErrors),
                ]);
                
            } catch (\Exception $e) {
                Log::error('PlantaCruds integration failed', [
                    'order_id' => $order->order_id,
                    'order_number' => $order->order_number,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                $integrationErrors[] = [
                    'error' => 'Error general de integración: ' . $e->getMessage(),
                ];
            }

            $response = [
                'message' => 'Pedido aprobado exitosamente',
                'order' => $order->load('orderProducts.product', 'approver'),
            ];
            
            if (!empty($enviosCreated)) {
                $response['envios_created'] = $enviosCreated;
                $response['integration_success'] = true;
            }
            
            if (!empty($integrationErrors)) {
                $response['integration_errors'] = $integrationErrors;
                $response['integration_partial_success'] = !empty($enviosCreated);
            }

            return response()->json($response);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al aprobar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}








