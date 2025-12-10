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
    public function index(Request $request)
    {
        $query = CustomerOrder::with(['customer', 'orderProducts.product']);
        
        // Filtro por estado - por defecto mostrar pendientes y aprobados
        $estadoFiltro = $request->get('estado', 'pendientes_aprobados');
        
        if ($estadoFiltro === 'pendientes_aprobados') {
            $query->whereIn('estado', ['pendiente', 'aprobado']);
        } elseif ($estadoFiltro && $estadoFiltro !== '') {
            $query->where('estado', $estadoFiltro);
        }
        
        // Filtro por cliente
        if ($request->has('cliente') && $request->cliente) {
            $query->whereHas('customer', function($q) use ($request) {
                $q->where('razon_social', 'like', '%' . $request->cliente . '%')
                  ->orWhere('nombre_comercial', 'like', '%' . $request->cliente . '%');
            });
        }
        
        // Filtro por fecha
        if ($request->has('fecha') && $request->fecha) {
            $query->whereDate('fecha_creacion', $request->fecha);
        }
        
        $pedidos = $query->orderBy('fecha_creacion', 'desc')->paginate(15);

        $clientes = Customer::where('activo', true)->get();

        // Estadísticas
        $stats = [
            'total' => CustomerOrder::count(),
            'pendientes' => CustomerOrder::where('estado', 'pendiente')->count(),
            'aprobados' => CustomerOrder::where('estado', 'aprobado')->count(),
            'rechazados' => CustomerOrder::where('estado', 'rechazado')->count(),
            'en_produccion' => CustomerOrder::where('estado', 'en_produccion')->count(),
        ];

        return view('gestion-pedidos', compact('pedidos', 'clientes', 'stats', 'estadoFiltro'));
    }

    public function show($id)
    {
        $pedido = CustomerOrder::with([
            'customer',
            'orderProducts.product.unit',
            'destinations.destinationProducts.orderProduct.product',
            'approver'
        ])->findOrFail($id);

        $trackings = OrderEnvioTracking::where('pedido_id', $pedido->pedido_id)->orderBy('created_at', 'desc')->get();

        // Base web URL of PlantaCruds (try to derive from API URL)
        $apiUrl = env('PLANTACRUDS_API_URL', 'http://localhost:8001/api');
        $plantaBase = rtrim(str_replace('/api', '', $apiUrl), '/');

        return view('gestion-pedidos-detalle', compact('pedido', 'trackings', 'plantaBase'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'fecha_entrega' => 'nullable|date',
            'descripcion' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $pedido = CustomerOrder::findOrFail($id);
            $pedido->update([
                'fecha_entrega' => $request->fecha_entrega,
                'descripcion' => $request->descripcion,
                'observaciones' => $request->observaciones,
            ]);

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
            
            if ($order->estado !== 'pendiente') {
                return redirect()->back()
                    ->with('error', 'Solo se pueden aprobar pedidos pendientes');
            }

            // Aprobar todos los productos del pedido
            OrderProduct::where('pedido_id', $orderId)
                ->where('estado', 'pendiente')
                ->update([
                    'estado' => 'aprobado',
                    'aprobado_por' => Auth::id(),
                    'aprobado_en' => now(),
                    'observaciones' => $request->observations,
                ]);

            // Aprobar el pedido completo
            $order->update([
                'estado' => 'aprobado',
                'aprobado_por' => Auth::id(),
                'aprobado_en' => now(),
                'observaciones' => $request->observations,
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
            
            if ($order->estado !== 'pendiente') {
                return redirect()->back()
                    ->with('error', 'Solo se pueden rechazar pedidos pendientes');
            }

            // Rechazar todos los productos del pedido
            OrderProduct::where('pedido_id', $orderId)
                ->where('estado', 'pendiente')
                ->update([
                    'estado' => 'rechazado',
                    'aprobado_por' => Auth::id(),
                    'aprobado_en' => now(),
                    'razon_rechazo' => $request->rejection_reason,
                ]);

            // Rechazar el pedido completo
            $order->update([
                'estado' => 'rechazado',
                'aprobado_por' => Auth::id(),
                'aprobado_en' => now(),
                'razon_rechazo' => $request->rejection_reason,
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

