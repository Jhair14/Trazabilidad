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
use Illuminate\Support\Facades\Http;

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
                ->with(['customer', 'orderProducts.product', 'batches'])
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

        // Obtener planta (origen) y almacenes destino desde plantaCruds
        $planta = null;
        $almacenesDestino = [];

        try {
            $apiUrl = env('PLANTACRUDS_API_URL', 'http://localhost/plantaCruds/public/api');
            $resp = Http::timeout(5)->get("{$apiUrl}/almacenes");
            if ($resp->successful()) {
                $almacenes = $resp->json('data', []);
                foreach ($almacenes as $alm) {
                    if (!empty($alm['es_planta']) && $alm['es_planta']) {
                        $planta = $alm;
                    } else {
                        $almacenesDestino[] = $alm;
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('No se pudieron obtener almacenes de plantaCruds: ' . $e->getMessage());
        }

        return view('crear-pedido', compact('products', 'planta', 'almacenesDestino'));
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
            'destinations.*.almacen_destino_id' => 'required|integer',
            'destinations.*.address' => 'nullable|string|max:500',
            'destinations.*.latitude' => 'nullable|numeric|between:-90,90',
            'destinations.*.longitude' => 'nullable|numeric|between:-180,180',
            'destinations.*.reference' => 'nullable|string|max:200',
            'destinations.*.contact_name' => 'nullable|string|max:200',
            'destinations.*.contact_phone' => 'nullable|string|max:20',
            'destinations.*.delivery_instructions' => 'nullable|string',
            'destinations.*.products' => 'required|array|min:1',
            'destinations.*.products.*.order_product_index' => 'required|integer|min:0',
            'destinations.*.products.*.quantity' => 'required|integer|min:1',
            'almacen_id' => 'nullable|integer',
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

            // Si el usuario seleccionó un almacen para el pedido, intentar resolver su nombre
            $almacenName = null;
            $selectedAlmacenId = $request->input('almacen_id');
            if (!empty($selectedAlmacenId)) {
                try {
                    $apiUrl = env('PLANTACRUDS_API_URL', 'http://localhost/plantaCruds/public/api');
                    $resp = Http::timeout(5)->get("{$apiUrl}/almacenes");
                    if ($resp->successful()) {
                        $almacenes = $resp->json('data', []);
                        foreach ($almacenes as $alm) {
                            if (isset($alm['id']) && $alm['id'] == $selectedAlmacenId) {
                                $almacenName = $alm['nombre'] ?? ($alm['nombre_comercial'] ?? null);
                                break;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('No se pudo resolver nombre de almacen seleccionado: ' . $e->getMessage());
                }
            }
            foreach ($request->destinations as $destIndex => $destData) {
                $destinationId = $maxDestinationId + $destIndex + 1;

                // Resolver nombre del almacén destino si se proporcionó
                $almacenDestinoNombre = null;
                $almacenDestinoId = $destData['almacen_destino_id'] ?? null;
                if (!empty($almacenDestinoId)) {
                    try {
                        $apiUrl = env('PLANTACRUDS_API_URL', 'http://localhost/plantaCruds/public/api');
                        $resp = Http::timeout(5)->get("{$apiUrl}/almacenes");
                        if ($resp->successful()) {
                            foreach ($resp->json('data', []) as $alm) {
                                if (isset($alm['id']) && $alm['id'] == $almacenDestinoId) {
                                    $almacenDestinoNombre = $alm['nombre'] ?? null;
                                    break;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning('No se pudo resolver nombre de almacen destino: ' . $e->getMessage());
                    }
                }

                $destination = OrderDestination::create([
                    'destination_id' => $destinationId,
                    'order_id' => $order->order_id,
                    'address' => $destData['address'] ?? $almacenDestinoNombre ?? 'Sin dirección',
                    'latitude' => $destData['latitude'] ?? null,
                    'longitude' => $destData['longitude'] ?? null,
                    'reference' => $destData['reference'] ?? null,
                    'contact_name' => $destData['contact_name'] ?? null,
                    'contact_phone' => $destData['contact_phone'] ?? null,
                    'delivery_instructions' => $destData['delivery_instructions'] ?? null,
                    'almacen_origen_id' => $selectedAlmacenId ?? null,
                    'almacen_origen_nombre' => $almacenName ?? null,
                    'almacen_destino_id' => $almacenDestinoId,
                    'almacen_destino_nombre' => $almacenDestinoNombre,
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

    public function show($id)
    {
        $user = Auth::user();
        $customerId = $this->getOrCreateCustomerId($user);

        $pedido = CustomerOrder::with([
            'customer',
            'orderProducts.product.unit',
            'destinations.destinationProducts.orderProduct.product',
            'approver',
            'batches'
        ])->findOrFail($id);

        // Verificar que el pedido pertenece al cliente del usuario
        if ($pedido->customer_id != $customerId) {
            abort(403, 'No tienes permiso para ver este pedido');
        }

        // Si es una petición AJAX, devolver JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'order_id' => $pedido->order_id,
                'order_number' => $pedido->order_number,
                'name' => $pedido->name,
                'description' => $pedido->description,
                'status' => $pedido->status,
                'creation_date' => $pedido->creation_date->format('Y-m-d'),
                'delivery_date' => $pedido->delivery_date ? $pedido->delivery_date->format('Y-m-d') : null,
                'priority' => $pedido->priority,
                'observations' => $pedido->observations,
                'editable_until' => $pedido->editable_until ? $pedido->editable_until->format('Y-m-d H:i:s') : null,
                'approved_at' => $pedido->approved_at ? $pedido->approved_at->format('Y-m-d H:i:s') : null,
                'can_be_edited' => $pedido->canBeEdited(),
                'orderProducts' => $pedido->orderProducts->map(function ($op) {
                    return [
                        'order_product_id' => $op->order_product_id,
                        'product_id' => $op->product_id,
                        'quantity' => $op->quantity,
                        'status' => $op->status,
                        'observations' => $op->observations,
                        'rejection_reason' => $op->rejection_reason,
                        'product' => [
                            'product_id' => $op->product->product_id,
                            'name' => $op->product->name ?? 'N/A',
                            'code' => $op->product->code ?? 'N/A',
                            'unit' => [
                                'name' => $op->product->unit->name ?? 'N/A',
                                'abbreviation' => $op->product->unit->code ?? 'N/A',
                            ]
                        ]
                    ];
                }),
                'destinations' => $pedido->destinations->map(function ($dest) {
                    return [
                        'address' => $dest->address,
                        'reference' => $dest->reference,
                        'contact_name' => $dest->contact_name,
                        'contact_phone' => $dest->contact_phone,
                        'delivery_instructions' => $dest->delivery_instructions,
                    ];
                }),
            ]);
        }

        return view('mis-pedidos-detalle', compact('pedido'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $customerId = $this->getOrCreateCustomerId($user);

        $pedido = CustomerOrder::with([
            'orderProducts.product.unit',
            'destinations.destinationProducts.orderProduct.product'
        ])->findOrFail($id);

        // Verificar que el pedido pertenece al cliente del usuario
        if ($pedido->customer_id != $customerId) {
            abort(403, 'No tienes permiso para editar este pedido');
        }

        // Verificar si el pedido puede ser editado
        if (!$pedido->canBeEdited()) {
            return redirect()->route('mis-pedidos')
                ->with('error', 'El pedido no puede ser editado. Ya fue aprobado o expiró el tiempo de edición.');
        }

        $products = Product::where('active', true)
            ->with('unit')
            ->orderBy('name')
            ->get();

        // Obtener planta y almacenes destino
        $planta = null;
        $almacenesDestino = [];
        try {
            $apiUrl = env('PLANTACRUDS_API_URL', 'http://localhost/plantaCruds/public/api');
            $resp = Http::timeout(5)->get("{$apiUrl}/almacenes");
            if ($resp->successful()) {
                foreach ($resp->json('data', []) as $alm) {
                    if (!empty($alm['es_planta']) && $alm['es_planta']) {
                        $planta = $alm;
                    } else {
                        $almacenesDestino[] = $alm;
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('No se pudieron obtener almacenes de plantaCruds en edit: ' . $e->getMessage());
        }

        return view('editar-pedido', compact('pedido', 'products', 'planta', 'almacenesDestino'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $customerId = $this->getOrCreateCustomerId($user);

        $pedido = CustomerOrder::findOrFail($id);

        // Verificar que el pedido pertenece al cliente del usuario
        if ($pedido->customer_id != $customerId) {
            abort(403, 'No tienes permiso para editar este pedido');
        }

        // Verificar si el pedido puede ser editado
        if (!$pedido->canBeEdited()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El pedido no puede ser editado. Ya fue aprobado o expiró el tiempo de edición.'
                ], 403);
            }
            return redirect()->back()
                ->with('error', 'El pedido no puede ser editado. Ya fue aprobado o expiró el tiempo de edición.')
                ->withInput();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
            'delivery_date' => 'nullable|date|after:today',
            'priority' => 'nullable|integer|min:1|max:10',
            'description' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer|exists:product,product_id',
            'products.*.quantity' => 'required|numeric|min:0.0001',
            'products.*.observations' => 'nullable|string',
            'destinations' => 'required|array|min:1',
            'destinations.*.almacen_destino_id' => 'required|integer',
            'destinations.*.address' => 'nullable|string|max:500',
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
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Actualizar información básica del pedido
            $pedido->update([
                'name' => $request->name,
                'delivery_date' => $request->delivery_date,
                'priority' => $request->priority ?? $pedido->priority,
                'description' => $request->description,
            ]);

            // Eliminar productos y destinos existentes
            $pedido->orderProducts()->delete();
            $pedido->destinations()->delete();

            // Crear nuevos productos del pedido
            $orderProducts = [];
            $maxOrderProductId = OrderProduct::max('order_product_id') ?? 0;

            foreach ($request->products as $index => $productData) {
                $orderProductId = $maxOrderProductId + $index + 1;

                $orderProduct = OrderProduct::create([
                    'order_product_id' => $orderProductId,
                    'order_id' => $pedido->order_id,
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'status' => 'pendiente',
                    'observations' => $productData['observations'] ?? null,
                ]);

                $orderProducts[] = $orderProduct;
                $maxOrderProductId = $orderProductId;
            }

            // Crear destinos y asignar productos
            $maxDestinationId = OrderDestination::max('destination_id') ?? 0;

            // Resolver nombre de almacen si se proporcionó en la petición
            $almacenName = null;
            $selectedAlmacenId = $request->input('almacen_id');
            if (!empty($selectedAlmacenId)) {
                try {
                    $apiUrl = env('PLANTACRUDS_API_URL', 'http://localhost/plantaCruds/public/api');
                    $resp = Http::timeout(5)->get("{$apiUrl}/almacenes");
                    if ($resp->successful()) {
                        $almacenes = $resp->json('data', []);
                        foreach ($almacenes as $alm) {
                            if (isset($alm['id']) && $alm['id'] == $selectedAlmacenId) {
                                $almacenName = $alm['nombre'] ?? ($alm['nombre_comercial'] ?? null);
                                break;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('No se pudo resolver nombre de almacen seleccionado (update): ' . $e->getMessage());
                }
            }
            foreach ($request->destinations as $destIndex => $destData) {
                $destinationId = $maxDestinationId + $destIndex + 1;

                // Resolver nombre del almacén destino si se proporcionó
                $almacenDestinoNombre = null;
                $almacenDestinoId = $destData['almacen_destino_id'] ?? null;
                if (!empty($almacenDestinoId)) {
                    try {
                        $apiUrl = env('PLANTACRUDS_API_URL', 'http://localhost/plantaCruds/public/api');
                        $resp = Http::timeout(5)->get("{$apiUrl}/almacenes");
                        if ($resp->successful()) {
                            foreach ($resp->json('data', []) as $alm) {
                                if (isset($alm['id']) && $alm['id'] == $almacenDestinoId) {
                                    $almacenDestinoNombre = $alm['nombre'] ?? null;
                                    break;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning('No se pudo resolver nombre de almacen destino (update): ' . $e->getMessage());
                    }
                }

                $destination = OrderDestination::create([
                    'destination_id' => $destinationId,
                    'order_id' => $pedido->order_id,
                    'address' => $destData['address'] ?? $almacenDestinoNombre ?? 'Sin dirección',
                    'latitude' => $destData['latitude'] ?? null,
                    'longitude' => $destData['longitude'] ?? null,
                    'reference' => $destData['reference'] ?? null,
                    'contact_name' => $destData['contact_name'] ?? null,
                    'contact_phone' => $destData['contact_phone'] ?? null,
                    'delivery_instructions' => $destData['delivery_instructions'] ?? null,
                    'almacen_origen_id' => $selectedAlmacenId ?? null,
                    'almacen_origen_nombre' => $almacenName ?? null,
                    'almacen_destino_id' => $almacenDestinoId,
                    'almacen_destino_nombre' => $almacenDestinoNombre,
                ]);

                // Asignar productos a este destino
                $maxDestProdId = OrderDestinationProduct::max('destination_product_id') ?? 0;
                foreach ($destData['products'] as $destProdIndex => $destProdData) {
                    $orderProductIndex = $destProdData['order_product_index'];
                    if (isset($orderProducts[$orderProductIndex])) {
                        $destProdId = $maxDestProdId + $destProdIndex + 1;
                        $maxDestProdId = $destProdId;

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

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pedido actualizado exitosamente'
                ]);
            }

            return redirect()->route('mis-pedidos')
                ->with('success', 'Pedido actualizado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar pedido: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar pedido: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error al actualizar pedido: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function cancel($id)
    {
        try {
            $user = Auth::user();
            $customerId = $this->getOrCreateCustomerId($user);

            $order = CustomerOrder::findOrFail($id);

            // Verificar que el pedido pertenece al cliente del usuario
            if ($order->customer_id != $customerId) {
                abort(403, 'No tienes permiso para cancelar este pedido');
            }

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
