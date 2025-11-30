<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PedidosController extends Controller
{
    public function misPedidos()
    {
        $user = Auth::user();
        
        // Cargar la relación role si no está cargada
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        // Buscar customer relacionado con el operador
        $customerId = $user->customer_id ?? null;
        $customer = null;
        
        if (!$customerId) {
            // Buscar por email
            $customer = Customer::where('email', $user->email)->first();
            $customerId = $customer ? $customer->customer_id : null;
        }
        
        // Si no se encontró un cliente, crear uno automáticamente para este usuario
        if (!$customerId) {
            try {
                // Sincronizar secuencia de customer si es necesario
                $maxCustomerId = Customer::max('customer_id') ?? 0;
                try {
                    $seqResult = DB::selectOne("SELECT last_value FROM customer_seq");
                    $seqValue = $seqResult->last_value ?? 0;
                } catch (\Exception $e) {
                    $seqValue = 0;
                }
                
                if ($seqValue < $maxCustomerId) {
                    DB::statement("SELECT setval('customer_seq', $maxCustomerId, true)");
                }
                
                // Obtener el siguiente ID de la secuencia
                $nextId = DB::selectOne("SELECT nextval('customer_seq') as id")->id;
                
                // Crear un Customer automáticamente para este operador
                $customer = Customer::create([
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
                $customer = Customer::where('active', true)->first();
                $customerId = $customer ? $customer->customer_id : null;
            }
        }
        
        // Si aún no hay customerId, mostrar pedidos vacíos
        if (!$customerId) {
            $pedidos = CustomerOrder::whereRaw('1 = 0')->paginate(15);
        } else {
            $pedidos = CustomerOrder::where('customer_id', $customerId)
                ->with('customer')
                ->orderBy('creation_date', 'desc')
                ->paginate(15);
        }

        // Estadísticas
        $stats = [
            'total' => $pedidos->total(),
            'pendientes' => $pedidos->where('priority', '>', 0)->count(),
            'en_proceso' => $pedidos->where('priority', '>', 0)->where('priority', '<=', 5)->count(),
            'completados' => $pedidos->where('priority', 0)->count(),
        ];

        return view('mis-pedidos', compact('pedidos', 'stats'));
    }

    public function crearPedido(Request $request)
    {
        $user = Auth::user();
        
        // Cargar la relación role si no está cargada
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        // Buscar customer relacionado con el operador
        $customerId = $user->customer_id ?? null;
        $customer = null;
        
        if (!$customerId) {
            // Buscar por email
            $customer = Customer::where('email', $user->email)->first();
            $customerId = $customer ? $customer->customer_id : null;
        }
        
        // Si no se encontró un cliente, crear uno automáticamente para este usuario
        if (!$customerId) {
            try {
                // Sincronizar secuencia de customer si es necesario
                $maxCustomerId = Customer::max('customer_id') ?? 0;
                try {
                    $seqResult = DB::selectOne("SELECT last_value FROM customer_seq");
                    $seqValue = $seqResult->last_value ?? 0;
                } catch (\Exception $e) {
                    $seqValue = 0;
                }
                
                if ($seqValue < $maxCustomerId) {
                    DB::statement("SELECT setval('customer_seq', $maxCustomerId, true)");
                }
                
                // Obtener el siguiente ID de la secuencia
                $nextId = DB::selectOne("SELECT nextval('customer_seq') as id")->id;
                
                // Crear un Customer automáticamente para este operador
                $customer = Customer::create([
                    'customer_id' => $nextId,
                    'business_name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'Cliente ' . $user->username,
                    'trading_name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'Cliente ' . $user->username,
                    'email' => $user->email ?? null,
                    'contact_person' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->username,
                    'active' => true,
                ]);
                
                $customerId = $customer->customer_id;
            } catch (\Exception $e) {
                // Si falla al crear, intentar usar el primer cliente activo disponible
                $customer = Customer::where('active', true)->first();
                if ($customer) {
                    $customerId = $customer->customer_id;
                } else {
                    return redirect()->back()
                        ->with('error', 'Error al crear cliente asociado: ' . $e->getMessage())
                        ->withInput();
                }
            }
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:0.0001',
            'delivery_date' => ['nullable', 'date', 'after_or_equal:today'],
            'priority' => 'nullable|integer|min:1|max:10',
            'description' => 'nullable|string',
            'observations' => 'nullable|string',
        ], [
            'delivery_date.after_or_equal' => 'La fecha de entrega no puede ser anterior a hoy.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Obtener el siguiente ID de la secuencia
            $nextId = DB::selectOne("SELECT nextval('customer_order_seq') as id")->id;
            
            // Generar número de pedido automáticamente
            $orderNumber = 'PED-' . str_pad($nextId, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd');
            
            CustomerOrder::create([
                'order_id' => $nextId,
                'customer_id' => $customerId,
                'order_number' => $orderNumber,
                'quantity' => $request->quantity,
                'creation_date' => now()->toDateString(),
                'delivery_date' => $request->delivery_date,
                'priority' => $request->priority ?? 1,
                'description' => $request->description,
                'observations' => $request->observations,
            ]);

            return redirect()->route('mis-pedidos')
                ->with('success', 'Pedido creado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear pedido: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        
        // Buscar customer relacionado con el operador
        $customerId = $user->customer_id ?? null;
        if (!$customerId) {
            $customer = Customer::where('email', $user->email)->first();
            $customerId = $customer ? $customer->customer_id : null;
        }
        
        try {
            $pedido = CustomerOrder::where('order_id', $id)
                ->where('customer_id', $customerId)
                ->with(['customer', 'batches'])
                ->firstOrFail();
            
            return response()->json([
                'order_id' => $pedido->order_id,
                'order_number' => $pedido->order_number,
                'quantity' => $pedido->quantity,
                'creation_date' => $pedido->creation_date,
                'delivery_date' => $pedido->delivery_date,
                'priority' => $pedido->priority,
                'description' => $pedido->description,
                'observations' => $pedido->observations,
                'customer' => $pedido->customer ? [
                    'business_name' => $pedido->customer->business_name,
                    'trading_name' => $pedido->customer->trading_name,
                    'email' => $pedido->customer->email,
                ] : null,
                'batches_count' => $pedido->batches->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }
    }
}
