<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\OrderProduct;
use App\Models\OrderDestination;
use App\Models\OrderDestinationProduct;
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

    public function crearPedidoForm()
    {
        $products = Product::where('active', true)
            ->with('unit')
            ->orderBy('name')
            ->get();
        
        return view('crear-pedido', compact('products'));
    }

    public function crearPedido(Request $request)
    {
        $user = Auth::user();
        
        // Obtener customer_id del usuario
        $customerId = $this->getOrCreateCustomerId($user);
        
        if (!$customerId) {
            return redirect()->back()
                ->with('error', 'No se pudo asociar un cliente a tu cuenta')
                ->withInput();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
            'delivery_date' => 'nullable|date|after:today',
            'priority' => 'nullable|integer|min:1|max:10',
            'description' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer|exists:product,product_id',
            'products.*.quantity' => 'required|integer|min:1',
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
            'destinations.*.products.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Obtener el siguiente ID
            $maxId = CustomerOrder::max('order_id') ?? 0;
            $nextId = $maxId + 1;
            
            // Generar número de pedido
            $orderNumber = 'PED-' . str_pad($nextId, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd');
            
            // Calcular fecha límite de edición (por defecto 24 horas)
            $editableUntil = now()->addHours(24);
            
            $order = CustomerOrder::create([
                'order_id' => $nextId,
                'customer_id' => $customerId,
                'order_number' => $orderNumber,
                'name' => $request->name,
                'status' => 'pendiente',
                'creation_date' => now()->toDateString(),
                'delivery_date' => $request->delivery_date,
                'priority' => $request->priority ?? 1,
                'description' => $request->description,
                'editable_until' => $editableUntil,
            ]);

            // Crear productos del pedido
            $orderProducts = [];
            $maxOrderProductId = OrderProduct::max('order_product_id') ?? 0;
            
            foreach ($request->products as $index => $productData) {
                $orderProductId = $maxOrderProductId + $index + 1;
                
                $orderProduct = OrderProduct::create([
                    'order_product_id' => $orderProductId,
                    'order_id' => $order->order_id,
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'status' => 'pendiente',
                    'observations' => $productData['observations'] ?? null,
                ]);
                
                $orderProducts[] = $orderProduct;
                $maxOrderProductId = $orderProductId; // Actualizar para el siguiente
            }

            // Crear destinos y asignar productos
            $maxDestinationId = OrderDestination::max('destination_id') ?? 0;
            foreach ($request->destinations as $destIndex => $destData) {
                $destinationId = $maxDestinationId + $destIndex + 1;
                
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
                $maxDestProdId = OrderDestinationProduct::max('destination_product_id') ?? 0;
                foreach ($destData['products'] as $destProdIndex => $destProdData) {
                    $orderProductIndex = $destProdData['order_product_index'];
                    if (isset($orderProducts[$orderProductIndex])) {
                        $destProdId = $maxDestProdId + $destProdIndex + 1;
                        $maxDestProdId = $destProdId; // Actualizar para el siguiente
                        
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

            return redirect()->route('mis-pedidos')
                ->with('success', 'Pedido creado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear pedido: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            return redirect()->back()
                ->with('error', 'Error al crear pedido: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function getOrCreateCustomerId($user)
    {
        $customerId = $user->customer_id ?? null;
        $customer = null;
        
        if (!$customerId) {
            $customer = Customer::where('email', $user->email)->first();
            $customerId = $customer ? $customer->customer_id : null;
        }
        
        if (!$customerId) {
            try {
                $maxCustomerId = Customer::max('customer_id') ?? 0;
                $nextId = $maxCustomerId + 1;
                
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
                $customer = Customer::where('active', true)->first();
                $customerId = $customer ? $customer->customer_id : null;
            }
        }
        
        return $customerId;
    }

    public function cancel($id)
    {
        try {
            $order = CustomerOrder::findOrFail($id);
            
            // Verificar si el pedido puede ser cancelado
            if (!$order->canBeEdited()) {
                return redirect()->back()
                    ->with('error', 'El pedido no puede ser cancelado. Ya fue aprobado o expiró el tiempo de edición.');
            }

            $order->update([
                'status' => 'cancelado'
            ]);

            return redirect()->route('mis-pedidos')
                ->with('success', 'Pedido cancelado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cancelar pedido: ' . $e->getMessage());
        }
    }
}
