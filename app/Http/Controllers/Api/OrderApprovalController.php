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

            $enviosCreated = [];
            $integrationErrors = [];
            $pedidoCompletado = false;

            if ($pendingProducts === 0) {
                // Todos los productos están aprobados, aprobar el pedido completo
                $order = CustomerOrder::findOrFail($orderId);
                $order->update([
                    'status' => 'aprobado',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);

                $pedidoCompletado = true;

                // Integración con plantaCruds cuando se aprueba el último producto
                $integrationService = new PlantaCrudsIntegrationService();
                
                try {
                    $results = $integrationService->sendOrderToShipping($order);
                    foreach ($results as $result) {
                        if ($result['success']) {
                            $enviosCreated[] = [
                                'destination_id' => $result['destination_id'],
                                'envio_codigo' => $result['envio_codigo'] ?? null,
                                'envio_id' => $result['envio_id'] ?? null,
                            ];
                        } else {
                            $integrationErrors[] = [
                                'destination_id' => $result['destination_id'],
                                'error' => $result['error'] ?? 'Error desconocido',
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error en integración con plantaCruds', [
                        'order_id' => $order->pedido_id,
                        'error' => $e->getMessage(),
                    ]);
                    $integrationErrors[] = ['error' => $e->getMessage()];
                }
            }

            DB::commit();

            $response = [
                'message' => 'Producto aprobado exitosamente',
                'order_product' => $orderProduct->load('product', 'approver')
            ];

            // Si se completó la aprobación del pedido, incluir información de envíos
            if ($pedidoCompletado) {
                $response['pedido_completado'] = true;
                if (!empty($enviosCreated)) {
                    $response['envios_created'] = $enviosCreated;
                    $response['integration_success'] = true;
                }
                if (!empty($integrationErrors)) {
                    $response['integration_errors'] = $integrationErrors;
                }
            }

            return response()->json($response);
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

            // Integración con plantaCruds
            $integrationService = new PlantaCrudsIntegrationService();
            $enviosCreated = [];
            $integrationErrors = [];
            
            try {
                $results = $integrationService->sendOrderToShipping($order);
                foreach ($results as $result) {
                    if ($result['success']) {
                        $enviosCreated[] = [
                            'destination_id' => $result['destination_id'],
                            'envio_codigo' => $result['envio_codigo'] ?? null,
                            'envio_id' => $result['envio_id'] ?? null,
                        ];
                    } else {
                        $integrationErrors[] = [
                            'destination_id' => $result['destination_id'],
                            'error' => $result['error'] ?? 'Error desconocido',
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error en integración con plantaCruds', [
                    'order_id' => $order->pedido_id,
                    'error' => $e->getMessage(),
                ]);
                $integrationErrors[] = ['error' => $e->getMessage()];
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










