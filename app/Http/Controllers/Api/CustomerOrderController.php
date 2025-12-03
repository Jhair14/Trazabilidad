<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\OrderProduct;
use App\Models\OrderDestination;
use App\Models\OrderDestinationProduct;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $orders = CustomerOrder::with('customer')
                ->orderBy('creation_date', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener pedidos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $order = CustomerOrder::with([
                'customer',
                'batches',
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

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer|exists:customer,customer_id',
            'name' => 'required|string|max:200',
            'delivery_date' => 'nullable|date',
            'priority' => 'nullable|integer|min:1|max:10',
            'description' => 'nullable|string',
            'observations' => 'nullable|string',
            'editable_until' => 'nullable|date|after:now',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer|exists:product,product_id',
            'products.*.quantity' => 'required|numeric|min:0.0001',
            'products.*.observations' => 'nullable|string',
            'destinations' => 'required|array|min:1',
            'destinations.*.address' => 'required|string|max:500',
            'destinations.*.latitude' => 'nullable|numeric|between:-90,90',
            'destinations.*.longitude' => 'nullable|numeric|between:-180,180',
            'destinations.*.reference' => 'nullable|string|max:200',
            'destinations.*.contact_name' => 'nullable|string|max:200',
            'destinations.*.contact_phone' => 'nullable|string|max:20',
            'destinations.*.delivery_instructions' => 'nullable|string',
            'destinations.*.products' => 'required|array|min:1',
            'destinations.*.products.*.order_product_index' => 'required|integer|min:0',
            'destinations.*.products.*.quantity' => 'required|numeric|min:0.0001',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Obtener el siguiente ID de la secuencia
            $maxId = DB::table('customer_order')->max('order_id') ?? 0;
            $nextId = $maxId + 1;
            
            // Generar número de pedido automáticamente
            $orderNumber = 'PED-' . str_pad($nextId, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd');
            
            // Calcular fecha límite de edición (por defecto 24 horas)
            $editableUntil = $request->editable_until 
                ? now()->parse($request->editable_until)
                : now()->addHours(24);
            
            $order = CustomerOrder::create([
                'order_id' => $nextId,
                'customer_id' => $request->customer_id,
                'order_number' => $orderNumber,
                'name' => $request->name,
                'status' => 'pendiente',
                'creation_date' => now()->toDateString(),
                'delivery_date' => $request->delivery_date,
                'priority' => $request->priority ?? 1,
                'description' => $request->description,
                'observations' => $request->observations,
                'editable_until' => $editableUntil,
            ]);

            // Crear productos del pedido
            $orderProducts = [];
            foreach ($request->products as $index => $productData) {
                $orderProductId = DB::table('order_product')->max('order_product_id') ?? 0;
                $orderProductId = $orderProductId + $index + 1;
                
                $orderProduct = OrderProduct::create([
                    'order_product_id' => $orderProductId,
                    'order_id' => $order->order_id,
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'status' => 'pendiente',
                    'observations' => $productData['observations'] ?? null,
                ]);
                
                $orderProducts[] = $orderProduct;
            }

            // Crear destinos y asignar productos
            foreach ($request->destinations as $destIndex => $destData) {
                $destinationId = DB::table('order_destination')->max('destination_id') ?? 0;
                $destinationId = $destinationId + $destIndex + 1;
                
                $destination = OrderDestination::create([
                    'destination_id' => $destinationId,
                    'order_id' => $order->order_id,
                    'address' => $destData['address'],
                    'latitude' => $destData['latitude'] ?? null,
                    'longitude' => $destData['longitude'] ?? null,
                    'reference' => $destData['reference'] ?? null,
                    'contact_name' => $destData['contact_name'] ?? null,
                    'contact_phone' => $destData['contact_phone'] ?? null,
                    'delivery_instructions' => $destData['delivery_instructions'] ?? null,
                ]);

                // Asignar productos a este destino
                foreach ($destData['products'] as $destProdIndex => $destProdData) {
                    $orderProductIndex = $destProdData['order_product_index'];
                    if (isset($orderProducts[$orderProductIndex])) {
                        $destProdId = DB::table('order_destination_product')->max('destination_product_id') ?? 0;
                        $destProdId = $destProdId + $destProdIndex + 1;
                        
                        OrderDestinationProduct::create([
                            'destination_product_id' => $destProdId,
                            'destination_id' => $destination->destination_id,
                            'order_product_id' => $orderProducts[$orderProductIndex]->order_product_id,
                            'quantity' => $destProdData['quantity'],
                            'observations' => $destProdData['observations'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Pedido creado exitosamente',
                'order' => $order->load('orderProducts.product', 'destinations')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:200',
            'delivery_date' => 'nullable|date',
            'priority' => 'nullable|integer|min:1|max:10',
            'description' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $order = CustomerOrder::findOrFail($id);

            // Verificar si el pedido puede ser editado
            if (!$order->canBeEdited()) {
                return response()->json([
                    'message' => 'El pedido no puede ser editado. Ya fue aprobado o expiró el tiempo de edición.'
                ], 403);
            }

            $order->update($request->only([
                'name', 'delivery_date', 'priority', 'description', 'observations'
            ]));

            return response()->json([
                'message' => 'Pedido actualizado exitosamente',
                'order' => $order->load('orderProducts.product', 'destinations')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cancel($id): JsonResponse
    {
        try {
            $order = CustomerOrder::findOrFail($id);

            // Verificar si el pedido puede ser cancelado
            if (!$order->canBeEdited()) {
                return response()->json([
                    'message' => 'El pedido no puede ser cancelado. Ya fue aprobado o expiró el tiempo de edición.'
                ], 403);
            }

            $order->update([
                'status' => 'cancelado'
            ]);

            return response()->json([
                'message' => 'Pedido cancelado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cancelar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $order = CustomerOrder::findOrFail($id);

            // Solo se puede eliminar si está pendiente y puede ser editado
            if (!$order->canBeEdited()) {
                return response()->json([
                    'message' => 'El pedido no puede ser eliminado. Ya fue aprobado o expiró el tiempo de edición.'
                ], 403);
            }

            $order->delete();

            return response()->json([
                'message' => 'Pedido eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

