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

            // Transformar para asegurar estructura consistente con frontend
            $data = $order->toArray();
            $data['orderProducts'] = $order->orderProducts->map(function($op) {
                return [
                    'producto_pedido_id' => $op->producto_pedido_id,
                    'producto_id' => $op->producto_id,
                    'cantidad' => $op->cantidad,
                    'estado' => $op->estado,
                    'observaciones' => $op->observaciones,
                    'razon_rechazo' => $op->razon_rechazo,
                    'product' => [
                        'producto_id' => $op->product->producto_id,
                        'nombre' => $op->product->nombre ?? 'N/A',
                        'codigo' => $op->product->codigo ?? 'N/A',
                        'unit' => [
                            'nombre' => $op->product->unit->nombre ?? 'N/A',
                            'abbreviation' => $op->product->unit->codigo ?? 'N/A',
                        ]
                    ]
                ];
            });

            return response()->json($data);
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
            'cliente_id' => 'required|integer|exists:cliente,cliente_id',
            'nombre' => 'required|string|max:200',
            'fecha_entrega' => 'nullable|date',
            'descripcion' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'editable_hasta' => 'nullable|date|after:now',
            'products' => 'required|array|min:1',
            'products.*.producto_id' => 'required|integer|exists:producto,producto_id',
            'products.*.cantidad' => 'required|numeric|min:0.0001',
            'products.*.observaciones' => 'nullable|string',
            'destinations' => 'required|array|min:1',
            'destinations.*.direccion' => 'required|string|max:500',
            'destinations.*.latitud' => 'nullable|numeric|between:-90,90',
            'destinations.*.longitud' => 'nullable|numeric|between:-180,180',
            'destinations.*.referencia' => 'nullable|string|max:200',
            'destinations.*.nombre_contacto' => 'nullable|string|max:200',
            'destinations.*.telefono_contacto' => 'nullable|string|max:20',
            'destinations.*.instrucciones_entrega' => 'nullable|string',
            'destinations.*.products' => 'required|array|min:1',
            'destinations.*.products.*.order_product_index' => 'required|integer|min:0',
            'destinations.*.products.*.cantidad' => 'required|numeric|min:0.0001',
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
            $maxId = DB::table('pedido_cliente')->max('pedido_id') ?? 0;
            if ($maxId > 0) {
                DB::statement("SELECT setval('pedido_cliente_seq', {$maxId}, true)");
            }
            $nextId = DB::selectOne("SELECT nextval('pedido_cliente_seq') as id")->id;
            
            // Generar número de pedido automáticamente
            $orderNumber = 'PED-' . str_pad($nextId, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd');
            
            // Calcular fecha límite de edición (por defecto 24 horas)
            $editableUntil = $request->editable_hasta 
                ? now()->parse($request->editable_hasta)
                : now()->addHours(24);
            
            $order = CustomerOrder::create([
                'pedido_id' => $nextId,
                'cliente_id' => $request->cliente_id,
                'numero_pedido' => $orderNumber,
                'nombre' => $request->nombre,
                'estado' => 'pendiente',
                'fecha_creacion' => now()->toDateString(),
                'fecha_entrega' => $request->fecha_entrega,
                'descripcion' => $request->descripcion,
                'observaciones' => $request->observaciones,
                'editable_hasta' => $editableUntil,
            ]);

            // Crear productos del pedido
            $orderProducts = [];
            foreach ($request->products as $index => $productData) {
                $maxProductId = DB::table('producto_pedido')->max('producto_pedido_id') ?? 0;
                if ($maxProductId > 0) {
                    DB::statement("SELECT setval('producto_pedido_seq', {$maxProductId}, true)");
                }
                $orderProductId = DB::selectOne("SELECT nextval('producto_pedido_seq') as id")->id;
                
                $orderProduct = OrderProduct::create([
                    'producto_pedido_id' => $orderProductId,
                    'pedido_id' => $order->pedido_id,
                    'producto_id' => $productData['producto_id'],
                    'cantidad' => $productData['cantidad'],
                    'estado' => 'pendiente',
                    'observaciones' => $productData['observaciones'] ?? null,
                ]);
                
                $orderProducts[] = $orderProduct;
            }

            // Crear destinos y asignar productos
            foreach ($request->destinations as $destIndex => $destData) {
                $maxDestId = DB::table('destino_pedido')->max('destino_id') ?? 0;
                if ($maxDestId > 0) {
                    DB::statement("SELECT setval('destino_pedido_seq', {$maxDestId}, true)");
                }
                $destinationId = DB::selectOne("SELECT nextval('destino_pedido_seq') as id")->id;
                
                $destination = OrderDestination::create([
                    'destino_id' => $destinationId,
                    'pedido_id' => $order->pedido_id,
                    'direccion' => $destData['direccion'],
                    'latitud' => $destData['latitud'] ?? null,
                    'longitud' => $destData['longitud'] ?? null,
                    'referencia' => $destData['referencia'] ?? null,
                    'nombre_contacto' => $destData['nombre_contacto'] ?? null,
                    'telefono_contacto' => $destData['telefono_contacto'] ?? null,
                    'instrucciones_entrega' => $destData['instrucciones_entrega'] ?? null,
                ]);

                // Asignar productos a este destino
                foreach ($destData['products'] as $destProdIndex => $destProdData) {
                    $orderProductIndex = $destProdData['order_product_index'];
                    if (isset($orderProducts[$orderProductIndex])) {
                        $maxDestProdId = DB::table('producto_destino_pedido')->max('producto_destino_id') ?? 0;
                        if ($maxDestProdId > 0) {
                            DB::statement("SELECT setval('producto_destino_pedido_seq', {$maxDestProdId}, true)");
                        }
                        $destProdId = DB::selectOne("SELECT nextval('producto_destino_pedido_seq') as id")->id;
                        
                        OrderDestinationProduct::create([
                            'producto_destino_id' => $destProdId,
                            'destino_id' => $destination->destino_id,
                            'producto_pedido_id' => $orderProducts[$orderProductIndex]->producto_pedido_id,
                            'cantidad' => $destProdData['cantidad'],
                            'observaciones' => $destProdData['observaciones'] ?? null,
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
                'name', 'delivery_date', 'description', 'observations'
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

