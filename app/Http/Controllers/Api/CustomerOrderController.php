<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
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
            $order = CustomerOrder::with(['customer', 'batches'])->findOrFail($id);
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
            'delivery_date' => 'nullable|date',
            'priority' => 'nullable|integer|min:1|max:10',
            'description' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'El ID del cliente es requerido',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('customer_order_seq') as id")->id;
            
            // Generar número de pedido automáticamente
            $orderNumber = 'PED-' . str_pad($nextId, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd');
            
            $order = CustomerOrder::create([
                'order_id' => $nextId,
                'customer_id' => $request->customer_id,
                'order_number' => $orderNumber,
                'creation_date' => now()->toDateString(),
                'delivery_date' => $request->delivery_date,
                'priority' => $request->priority ?? 1,
                'description' => $request->description,
                'observations' => $request->observations,
            ]);

            return response()->json([
                'message' => 'Pedido creado exitosamente',
                'id' => $order->order_id
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
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
            $order->update($request->only([
                'delivery_date', 'priority', 'description', 'observations'
            ]));

            return response()->json([
                'message' => 'Pedido actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $order = CustomerOrder::findOrFail($id);
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

