<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\Customer;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GestionPedidosController extends Controller
{
    public function index()
    {
        $pedidos = CustomerOrder::with(['customer', 'orderProducts.product'])
            ->where('status', 'pendiente')
            ->orderBy('creation_date', 'desc')
            ->paginate(15);

        $clientes = Customer::where('active', true)->get();

        // Estadísticas
        $stats = [
            'total' => CustomerOrder::count(),
            'pendientes' => CustomerOrder::where('status', 'pendiente')->count(),
            'aprobados' => CustomerOrder::where('status', 'aprobado')->count(),
            'rechazados' => CustomerOrder::where('status', 'rechazado')->count(),
            'en_produccion' => CustomerOrder::where('status', 'en_produccion')->count(),
        ];

        return view('gestion-pedidos', compact('pedidos', 'clientes', 'stats'));
    }

    public function show($id)
    {
        $pedido = CustomerOrder::with([
            'customer',
            'orderProducts.product.unit',
            'destinations.destinationProducts.orderProduct.product',
            'approver'
        ])->findOrFail($id);

        return view('gestion-pedidos-detalle', compact('pedido'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'delivery_date' => 'nullable|date',
            'priority' => 'nullable|integer|min:1|max:10',
            'description' => 'nullable|string',
            'observations' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $pedido = CustomerOrder::findOrFail($id);
            $pedido->update($request->only([
                'delivery_date', 'priority', 'description', 'observations'
            ]));

            return redirect()->route('gestion-pedidos')
                ->with('success', 'Pedido actualizado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar pedido: ' . $e->getMessage());
        }
    }

    public function approveProduct(Request $request, $orderId, $productId)
    {
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
                $order = CustomerOrder::findOrFail($orderId);
                $order->update([
                    'status' => 'aprobado',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('gestion-pedidos.show', $orderId)
                ->with('success', 'Producto aprobado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al aprobar producto: ' . $e->getMessage());
        }
    }

    public function rejectProduct(Request $request, $orderId, $productId)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
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

            DB::commit();

            return redirect()->route('gestion-pedidos.show', $orderId)
                ->with('success', 'Producto rechazado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al rechazar producto: ' . $e->getMessage());
        }
    }
}

