<?php

namespace App\Services;

use App\Models\CustomerOrder;
use App\Models\OrderDestination;
use App\Services\AlmacenSyncService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlantaCrudsIntegrationService
{
    private string $apiUrl;

    public function __construct()
    {
        // Usar config() en lugar de env() directamente para mejor rendimiento
        $this->apiUrl = config('services.plantacruds.api_url');
        
        // Validar que la URL esté configurada
        if (empty($this->apiUrl) || $this->apiUrl === 'http://localhost:8001/api') {
            Log::warning('PLANTACRUDS_API_URL no está configurada correctamente en .env', [
                'current_url' => $this->apiUrl,
                'env_value' => env('PLANTACRUDS_API_URL'),
            ]);
        }
        
        Log::info('PlantaCrudsIntegrationService inicializado', [
            'api_url' => $this->apiUrl,
        ]);
    }

    /**
     * Send approved order to PlantaCruds for shipping
     * 
     * @param CustomerOrder $order
     * @param \App\Models\Storage|null $storage Storage record with pickup location
     * @return array Array of results, one per destination
     */
    public function sendOrderToShipping(CustomerOrder $order, ?\App\Models\Storage $storage = null): array
    {
        // Load all relations
        $order->load([
            'customer',
            'orderProducts.product.unit',
            'destinations.destinationProducts.orderProduct.product'
        ]);

        $results = [];

        // Create one Envio per destination
        foreach ($order->destinations as $destination) {
            try {
                $envioData = $this->buildEnvioData($order, $destination, $storage);
                $response = $this->createEnvio($envioData);

                $results[] = [
                    'destination_id' => $destination->destino_id,
                    'success' => true,
                    'envio_id' => $response['data']['id'] ?? null,
                    'envio_codigo' => $response['data']['codigo'] ?? null,
                    'qr_code' => $response['qr_code'] ?? null,
                    'response' => $response,
                ];

                Log::info('Envio created successfully in plantaCruds', [
                    'order_id' => $order->pedido_id,
                    'order_number' => $order->numero_pedido,
                    'destination_id' => $destination->destino_id,
                    'envio_id' => $response['data']['id'] ?? null,
                    'envio_codigo' => $response['data']['codigo'] ?? null,
                ]);

            } catch (\Exception $e) {
                $results[] = [
                    'destination_id' => $destination->destino_id,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];

                Log::error('Failed to create Envio in plantaCruds', [
                    'order_id' => $order->pedido_id,
                    'order_number' => $order->numero_pedido,
                    'destination_id' => $destination->destino_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Build Envio data from order and destination
     * 
     * @param CustomerOrder $order
     * @param OrderDestination $destination
     * @param \App\Models\Storage|null $storage Storage record with pickup location
     * @return array
     */
    private function buildEnvioData(CustomerOrder $order, OrderDestination $destination, ?\App\Models\Storage $storage = null): array
    {
        $almacenSyncService = new AlmacenSyncService();
        
        // Prioridad: almacen_destino_id (seleccionado en UI) > buscar por coordenadas > usar default
        if (!empty($destination->almacen_destino_id)) {
            // Usar el almacén destino seleccionado directamente desde la UI
            $almacenId = $destination->almacen_destino_id;
            
            // Verificar que el almacén existe
            $almacen = $almacenSyncService->findAlmacenById($almacenId);
            if (!$almacen) {
                Log::warning('Almacén destino no encontrado, buscando alternativo', [
                    'almacen_id' => $almacenId,
                    'destination_id' => $destination->destino_id
                ]);
                $almacenId = $this->findAlmacenForDestination($destination, $almacenSyncService);
            }
        } else {
            // Buscar almacén basado en la ubicación del destino
            $almacenId = $this->findAlmacenForDestination($destination, $almacenSyncService);
        }

        // Build products array
        $productos = [];
        foreach ($destination->destinationProducts as $destProduct) {
            $orderProduct = $destProduct->orderProduct;
            $product = $orderProduct->product;

            $productos[] = [
                'producto_id' => null, // Not used, will rely on producto_nombre
                'producto_nombre' => $product->nombre,
                'cantidad' => (float) $destProduct->cantidad,
                'peso_kg' => (float) ($product->peso ?? 0),
                'precio' => 0.00, // Default price, can be adjusted
            ];
        }

        return [
            'almacen_destino_id' => $almacenId,
            'categoria' => 'general',
            'fecha_estimada_entrega' => $order->fecha_entrega ?? now()->addDays(3)->format('Y-m-d'),
            'hora_estimada' => '14:00', // Default time
            'observaciones' => $this->buildObservations($order, $destination, $storage),
            'productos' => $productos,
            'origen' => 'trazabilidad',
            'pedido_trazabilidad_id' => $order->pedido_id,
            'numero_pedido_trazabilidad' => $order->numero_pedido,
        ];
    }

    /**
     * Build observations text
     * 
     * @param CustomerOrder $order
     * @param OrderDestination $destination
     * @param \App\Models\Storage|null $storage Storage record with pickup location
     * @return string
     */
    private function buildObservations(CustomerOrder $order, OrderDestination $destination, ?\App\Models\Storage $storage = null): string
    {
        $obs = "Pedido: {$order->numero_pedido}\n";
        $obs .= "Cliente: {$order->customer->razon_social}\n";

        if ($order->observaciones) {
            $obs .= "Notas: {$order->observaciones}\n";
        }

        // Agregar información de ubicación de recojo si está disponible
        if ($storage && $storage->direccion_recojo) {
            $obs .= "\nUBICACIÓN DE RECOJO:\n";
            $obs .= "Dirección: {$storage->direccion_recojo}\n";
            if ($storage->referencia_recojo) {
                $obs .= "Referencia: {$storage->referencia_recojo}\n";
            }
            if ($storage->latitud_recojo && $storage->longitud_recojo) {
                $obs .= "Coordenadas: {$storage->latitud_recojo}, {$storage->longitud_recojo}\n";
            }
        }

        if ($destination->instrucciones_entrega) {
            $obs .= "\nInstrucciones de entrega: {$destination->instrucciones_entrega}\n";
        }

        if ($destination->nombre_contacto) {
            $obs .= "Contacto: {$destination->nombre_contacto}";
            if ($destination->telefono_contacto) {
                $obs .= " - Tel: {$destination->telefono_contacto}";
            }
            $obs .= "\n";
        }

        if ($destination->direccion) {
            $obs .= "Dirección de entrega: {$destination->direccion}";
            if ($destination->referencia) {
                $obs .= " ({$destination->referencia})";
            }
        }

        return trim($obs);
    }

    /**
     * Buscar almacén para un destino
     * 
     * @param OrderDestination $destination
     * @param AlmacenSyncService $almacenSyncService
     * @return int
     * @throws \Exception
     */
    private function findAlmacenForDestination(OrderDestination $destination, AlmacenSyncService $almacenSyncService): int
    {
        // Si el destino tiene coordenadas, buscar el almacén más cercano
        if ($destination->latitud && $destination->longitud) {
            $nearestAlmacen = $almacenSyncService->findNearestAlmacen(
                $destination->latitud,
                $destination->longitud,
                true // Solo almacenes de destino (no plantas)
            );

            if ($nearestAlmacen) {
                Log::info('Almacén encontrado por proximidad', [
                    'almacen_id' => $nearestAlmacen['id'],
                    'almacen_nombre' => $nearestAlmacen['nombre'],
                    'destination_id' => $destination->destino_id,
                ]);
                return $nearestAlmacen['id'];
            }
        }

        // Si no hay coordenadas o no se encontró, buscar por dirección
        if ($destination->direccion) {
            $almacenes = $almacenSyncService->getDestinoAlmacenes();
            
            foreach ($almacenes as $almacen) {
                $almacenAddress = $almacen['direccion'] ?? $almacen['nombre'] ?? '';
                if (
                    stripos($almacenAddress, $destination->direccion) !== false ||
                    stripos($destination->direccion, $almacenAddress) !== false
                ) {
                    Log::info('Almacén encontrado por coincidencia de dirección', [
                        'almacen_id' => $almacen['id'],
                        'almacen_nombre' => $almacen['nombre'],
                        'destination_id' => $destination->destino_id,
                    ]);
                    return $almacen['id'];
                }
            }
        }

        // Si no se encontró, usar el primer almacén de destino disponible
        $almacenes = $almacenSyncService->getDestinoAlmacenes();
        if (!empty($almacenes)) {
            $firstAlmacen = reset($almacenes);
            Log::warning('No se encontró almacén específico, usando almacén por defecto', [
                'almacen_id' => $firstAlmacen['id'],
                'almacen_nombre' => $firstAlmacen['nombre'],
                'destination_id' => $destination->destino_id,
                'destination_address' => $destination->direccion,
            ]);
            return $firstAlmacen['id'];
        }

        throw new \Exception("No hay almacenes de destino disponibles en plantaCruds para el destino: {$destination->direccion}");
    }


    /**
     * Send POST request to create Envio
     * 
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function createEnvio(array $data): array
    {
        Log::info('Sending envio data to plantaCruds', [
            'url' => "{$this->apiUrl}/envios",
            'data' => $data,
        ]);

        $response = Http::timeout(30)
            ->post("{$this->apiUrl}/envios", $data);

        if (!$response->successful()) {
            $errorBody = $response->body();
            Log::error('plantaCruds API request failed', [
                'status' => $response->status(),
                'body' => $errorBody,
            ]);
            throw new \Exception("Error al crear envío en plantaCruds (HTTP {$response->status()}): {$errorBody}");
        }

        $result = $response->json();

        if (!($result['success'] ?? false)) {
            throw new \Exception($result['message'] ?? 'Error desconocido al crear envío');
        }

        return $result;
    }
}
