<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\Customer;
use App\Models\OrderProduct;
use App\Models\OrderEnvioTracking;
use App\Services\PlantaCrudsIntegrationService;
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

        $trackings = OrderEnvioTracking::where('order_id', $pedido->order_id)->orderBy('created_at', 'desc')->get();

        // Base web URL of PlantaCruds (try to derive from API URL)
        $apiUrl = env('PLANTACRUDS_API_URL', 'http://localhost/plantaCruds/public/api');
        $plantaBase = rtrim(str_replace('/api', '', $apiUrl), '/');

        return view('gestion-pedidos-detalle', compact('pedido', 'trackings', 'plantaBase'));
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

    public function approveOrder(Request $request, $orderId)
    {
        $validator = Validator::make($request->all(), [
            'observations' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $order = CustomerOrder::findOrFail($orderId);
            
            if ($order->status !== 'pendiente') {
                return redirect()->back()
                    ->with('error', 'Solo se pueden aprobar pedidos pendientes');
            }

            // Aprobar todos los productos del pedido
            OrderProduct::where('order_id', $orderId)
                ->where('status', 'pendiente')
                ->update([
                    'status' => 'aprobado',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'observations' => $request->observations,
                ]);

            // Aprobar el pedido completo
            $order->update([
                'status' => 'aprobado',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'observations' => $request->observations,
            ]);

            DB::commit();

            // NOTA: El envío a plantaCruds ahora se realiza al almacenar el lote, no al aprobar el pedido

            return redirect()->route('gestion-pedidos.show', $orderId)
                ->with('success', 'Pedido aprobado exitosamente. El envío se creará cuando se almacene el lote.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al aprobar pedido: ' . $e->getMessage());
        }
    }

    public function rejectOrder(Request $request, $orderId)
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

            $order = CustomerOrder::findOrFail($orderId);
            
            if ($order->status !== 'pendiente') {
                return redirect()->back()
                    ->with('error', 'Solo se pueden rechazar pedidos pendientes');
            }

            // Rechazar todos los productos del pedido
            OrderProduct::where('order_id', $orderId)
                ->where('status', 'pendiente')
                ->update([
                    'status' => 'rechazado',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'rejection_reason' => $request->rejection_reason,
                ]);

            // Rechazar el pedido completo
            $order->update([
                'status' => 'rechazado',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            DB::commit();

            return redirect()->route('gestion-pedidos.show', $orderId)
                ->with('success', 'Pedido rechazado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al rechazar pedido: ' . $e->getMessage());
        }
    }
}

