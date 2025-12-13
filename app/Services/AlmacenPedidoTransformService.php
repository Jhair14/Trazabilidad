<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\OrderProduct;
use App\Models\OrderDestination;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlmacenPedidoTransformService
{
    /**
     * Transforma un pedido de sistema-almacen-PSIII a estructura de Trazabilidad
     * 
     * @param array $pedidoData Datos del pedido desde sistema-almacen-PSIII
     * @return array Datos transformados listos para crear CustomerOrder
     */
    public function transformToCustomerOrder(array $pedidoData): array
    {
        // Crear o buscar cliente basado en administrador/almacén
        $customer = $this->findOrCreateCustomer($pedidoData['administrador'] ?? []);

        // Generar número de pedido único
        $numeroPedido = $this->generateOrderNumber($pedidoData['codigo_comprobante'] ?? null);

        return [
            'cliente_id' => $customer->cliente_id,
            'numero_pedido' => $numeroPedido,
            'nombre' => $pedidoData['almacen']['nombre'] ?? 'Pedido desde Almacén',
            'estado' => 'pendiente',
            'fecha_creacion' => $pedidoData['fecha'] ?? now()->format('Y-m-d'),
            'fecha_entrega' => $pedidoData['fecha_max'] ?? now()->addDays(7)->format('Y-m-d'),
            'descripcion' => "Pedido desde Sistema Almacén - {$pedidoData['codigo_comprobante']}",
            'observaciones' => $this->buildObservations($pedidoData),
            'origen_sistema' => 'almacen',
            'pedido_almacen_id' => $pedidoData['pedido_id'] ?? null,
        ];
    }

    /**
     * Crea o busca un cliente basado en datos del administrador
     * 
     * @param array $administradorData
     * @return Customer
     */
    private function findOrCreateCustomer(array $administradorData): Customer
    {
        $email = $administradorData['email'] ?? null;
        $fullName = $administradorData['full_name'] ?? 'Cliente Almacén';

        if ($email) {
            // Buscar por email
            $customer = Customer::where('email', $email)->first();
            if ($customer) {
                return $customer;
            }
        }

        // Crear nuevo cliente
        $customer = Customer::create([
            'razon_social' => $fullName,
            'nombre_comercial' => $fullName,
            'email' => $email,
            'telefono' => $administradorData['phone_number'] ?? null,
            'direccion' => null,
            'activo' => true,
        ]);

        Log::info('Cliente creado desde pedido de almacén', [
            'cliente_id' => $customer->cliente_id,
            'razon_social' => $fullName,
            'email' => $email
        ]);

        return $customer;
    }

    /**
     * Genera número de pedido único
     * 
     * @param string|null $codigoComprobante
     * @return string
     */
    private function generateOrderNumber(?string $codigoComprobante): string
    {
        if ($codigoComprobante) {
            return "ALM-{$codigoComprobante}";
        }

        $lastOrder = CustomerOrder::orderBy('pedido_id', 'desc')->first();
        $nextId = $lastOrder ? $lastOrder->pedido_id + 1 : 1;
        
        return "ALM-PED-" . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Construye observaciones completas del pedido
     * 
     * @param array $pedidoData
     * @return string
     */
    private function buildObservations(array $pedidoData): string
    {
        $obs = "Pedido desde Sistema Almacén\n";
        $obs .= "Código: {$pedidoData['codigo_comprobante']}\n";
        $obs .= "Fecha: {$pedidoData['fecha']}\n";
        $obs .= "Fecha mínima: {$pedidoData['fecha_min']}\n";
        $obs .= "Fecha máxima: {$pedidoData['fecha_max']}\n";

        if (isset($pedidoData['administrador']['full_name'])) {
            $obs .= "Solicitante: {$pedidoData['administrador']['full_name']}";
            if (isset($pedidoData['administrador']['email'])) {
                $obs .= " ({$pedidoData['administrador']['email']})";
            }
            $obs .= "\n";
        }

        if (isset($pedidoData['operador']['full_name'])) {
            $obs .= "Operador: {$pedidoData['operador']['full_name']}\n";
        }

        if (isset($pedidoData['transportista']['full_name'])) {
            $obs .= "Transportista: {$pedidoData['transportista']['full_name']}\n";
        }

        if (isset($pedidoData['proveedor_id'])) {
            $obs .= "Proveedor ID: {$pedidoData['proveedor_id']}\n";
        }

        if (isset($pedidoData['observaciones'])) {
            $obs .= "\nNotas adicionales:\n{$pedidoData['observaciones']}";
        }

        return trim($obs);
    }

    /**
     * Crea productos del pedido en Trazabilidad
     * 
     * @param CustomerOrder $order
     * @param array $productosData
     * @return array Array de OrderProduct creados
     */
    public function createOrderProducts(CustomerOrder $order, array $productosData): array
    {
        $orderProducts = [];

        foreach ($productosData as $productoData) {
            // Buscar o crear producto
            $product = $this->findOrCreateProduct($productoData);

            // Crear OrderProduct
            $orderProduct = OrderProduct::create([
                'pedido_id' => $order->pedido_id,
                'producto_id' => $product->producto_id,
                'cantidad' => (float) ($productoData['cantidad'] ?? 0),
                'precio' => (float) ($productoData['precio'] ?? 0.00),
                'estado' => 'pendiente',
            ]);

            $orderProducts[] = $orderProduct;
        }

        return $orderProducts;
    }

    /**
     * Busca o crea un producto en Trazabilidad
     * 
     * @param array $productoData
     * @return Product
     */
    private function findOrCreateProduct(array $productoData): Product
    {
        $productoNombre = $productoData['producto_nombre'] ?? 'Producto sin nombre';
        $productoId = $productoData['producto_id'] ?? null;

        // Si hay producto_id, intentar buscar por ID primero (si hay sincronización)
        if ($productoId) {
            $product = Product::find($productoId);
            if ($product) {
                return $product;
            }
        }

        // Buscar por nombre
        $product = Product::where('nombre', $productoNombre)->first();
        if ($product) {
            return $product;
        }

        // Crear nuevo producto
        $product = Product::create([
            'codigo' => 'ALM-' . strtoupper(substr($productoNombre, 0, 3)) . '-' . time(),
            'nombre' => $productoNombre,
            'tipo' => 'general',
            'peso' => (float) ($productoData['peso_kg'] ?? $productoData['peso'] ?? 0),
            'precio_unitario' => (float) ($productoData['precio'] ?? 0),
            'descripcion' => "Producto importado desde Sistema Almacén",
            'activo' => true,
        ]);

        Log::info('Producto creado desde pedido de almacén', [
            'producto_id' => $product->producto_id,
            'nombre' => $productoNombre
        ]);

        return $product;
    }

    /**
     * Crea destino del pedido (almacén como destino)
     * 
     * @param CustomerOrder $order
     * @param array $almacenData
     * @param array|null $operadorData
     * @return OrderDestination
     */
    public function createOrderDestination(CustomerOrder $order, array $almacenData, ?array $operadorData = null): OrderDestination
    {
        return OrderDestination::create([
            'pedido_id' => $order->pedido_id,
            'direccion' => $almacenData['direccion'] ?? $almacenData['nombre'] ?? 'Dirección no especificada',
            'latitud' => $almacenData['latitud'] ?? null,
            'longitud' => $almacenData['longitud'] ?? null,
            'nombre_contacto' => $operadorData['full_name'] ?? null,
            'telefono_contacto' => null,
            'instrucciones_entrega' => "Entrega en almacén: {$almacenData['nombre']}",
        ]);
    }
}

