<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GestionPedidosController extends Controller
{
    public function index()
    {
        $pedidos = CustomerOrder::with('customer')
            ->orderBy('creation_date', 'desc')
            ->paginate(15);

        $clientes = Customer::where('active', true)->get();

        // Estadísticas
        $stats = [
            'total' => CustomerOrder::count(),
            'pendientes' => CustomerOrder::where('priority', '>', 0)->count(),
            'completados' => CustomerOrder::where('priority', 0)->count(),
            'en_proceso' => CustomerOrder::where('priority', '>', 0)->where('priority', '<=', 5)->count(),
        ];

        return view('gestion-pedidos', compact('pedidos', 'clientes', 'stats'));
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
}

