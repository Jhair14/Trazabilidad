<?php

namespace App\Services;

use App\Models\CustomerOrder;
use App\Models\OrderDestination;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlantaCrudsIntegrationService
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('PLANTACRUDS_API_URL', 'http://localhost/plantaCruds/public/api');
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
                    'destination_id' => $destination->destination_id,
                    'success' => true,
                    'envio_id' => $response['data']['id'] ?? null,
                    'envio_codigo' => $response['data']['codigo'] ?? null,
                    'qr_code' => $response['qr_code'] ?? null,
                    'response' => $response,
                ];

                Log::info('Envio created successfully in plantaCruds', [
                    'order_id' => $order->order_id,
                    'order_number' => $order->order_number,
                    'destination_id' => $destination->destination_id,
                    'envio_id' => $response['data']['id'] ?? null,
                    'envio_codigo' => $response['data']['codigo'] ?? null,
                ]);

            } catch (\Exception $e) {
                $results[] = [
                    'destination_id' => $destination->destination_id,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];

                Log::error('Failed to create Envio in plantaCruds', [
                    'order_id' => $order->order_id,
                    'order_number' => $order->order_number,
                    'destination_id' => $destination->destination_id,
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
        // Prioridad: almacen_destino_id (seleccionado en UI) > almacen_origen_id (legacy) > buscar/crear
        if (!empty($destination->almacen_destino_id)) {
            // Nuevo: Usar el almacén destino seleccionado directamente desde la UI
            $almacenId = $destination->almacen_destino_id;
        } elseif (!empty($destination->almacen_origen_id)) {
            // Legacy: almacen_origen_id si fue seteado
            $almacenId = $destination->almacen_origen_id;
        } else {
            // Fallback: buscar o crear almacén basado en la ubicación
            // Si hay storage con ubicación de recojo, usar esa ubicación
            if ($storage && $storage->pickup_latitude && $storage->pickup_longitude) {
                $almacenId = $this->findOrCreateAlmacenByCoordinates(
                    $storage->pickup_latitude,
                    $storage->pickup_longitude,
                    $storage->pickup_address
                );
            } else {
                $almacenId = $this->findOrCreateAlmacen($destination);
            }
        }

        // Build products array
        $productos = [];
        foreach ($destination->destinationProducts as $destProduct) {
            $orderProduct = $destProduct->orderProduct;
            $product = $orderProduct->product;

            $productos[] = [
                'producto_id' => null, // Not used, will rely on producto_nombre
                'producto_nombre' => $product->name,
                'cantidad' => (float) $destProduct->quantity,
                'peso_kg' => (float) ($product->weight ?? 0),
                'precio' => 0.00, // Default price, can be adjusted
            ];
        }

        return [
            'almacen_destino_id' => $almacenId,
            'categoria' => 'general',
            'fecha_estimada_entrega' => $order->delivery_date ?? now()->addDays(3)->format('Y-m-d'),
            'hora_estimada' => '14:00', // Default time
            'observaciones' => $this->buildObservations($order, $destination, $storage),
            'productos' => $productos,
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
        $obs = "Pedido: {$order->order_number}\n";
        $obs .= "Cliente: {$order->customer->business_name}\n";

        if ($order->observations) {
            $obs .= "Notas: {$order->observations}\n";
        }

        // Agregar información de ubicación de recojo si está disponible
        if ($storage && $storage->pickup_address) {
            $obs .= "\n📍 UBICACIÓN DE RECOJO:\n";
            $obs .= "Dirección: {$storage->pickup_address}\n";
            if ($storage->pickup_reference) {
                $obs .= "Referencia: {$storage->pickup_reference}\n";
            }
            if ($storage->pickup_latitude && $storage->pickup_longitude) {
                $obs .= "Coordenadas: {$storage->pickup_latitude}, {$storage->pickup_longitude}\n";
            }
        }

        if ($destination->delivery_instructions) {
            $obs .= "\nInstrucciones de entrega: {$destination->delivery_instructions}\n";
        }

        if ($destination->contact_name) {
            $obs .= "Contacto: {$destination->contact_name}";
            if ($destination->contact_phone) {
                $obs .= " - Tel: {$destination->contact_phone}";
            }
            $obs .= "\n";
        }

        if ($destination->address) {
            $obs .= "Dirección de entrega: {$destination->address}";
            if ($destination->reference) {
                $obs .= " ({$destination->reference})";
            }
        }

        return trim($obs);
    }

    /**
     * Find almacen by coordinates
     * 
     * @param float $latitude
     * @param float $longitude
     * @param string|null $address
     * @return int
     * @throws \Exception
     */
    private function findOrCreateAlmacenByCoordinates(float $latitude, float $longitude, ?string $address = null): int
    {
        try {
            $response = Http::timeout(10)->get("{$this->apiUrl}/almacenes");

            if ($response->successful()) {
                $almacenes = $response->json('data', []);

                // Try to find by coordinates
                foreach ($almacenes as $almacen) {
                    if (
                        isset($almacen['latitud']) && isset($almacen['longitud']) &&
                        $this->coordinatesMatch(
                            $almacen['latitud'],
                            $almacen['longitud'],
                            $latitude,
                            $longitude
                        )
                    ) {
                        Log::info('Found almacen by pickup coordinates', [
                            'almacen_id' => $almacen['id'],
                            'almacen_nombre' => $almacen['nombre'],
                        ]);
                        return $almacen['id'];
                    }
                }

                // Try to find by address match
                if ($address) {
                    foreach ($almacenes as $almacen) {
                        $almacenAddress = $almacen['direccion_completa'] ?? $almacen['nombre'] ?? '';
                        if (
                            stripos($almacenAddress, $address) !== false ||
                            stripos($address, $almacenAddress) !== false
                        ) {
                            Log::info('Found almacen by pickup address match', [
                                'almacen_id' => $almacen['id'],
                                'almacen_nombre' => $almacen['nombre'],
                            ]);
                            return $almacen['id'];
                        }
                    }
                }

                // Use first active non-plant almacen
                foreach ($almacenes as $almacen) {
                    if (($almacen['activo'] ?? true) && !($almacen['es_planta'] ?? false)) {
                        Log::warning('No matching almacen found for pickup location, using default', [
                            'almacen_id' => $almacen['id'],
                            'pickup_address' => $address,
                        ]);
                        return $almacen['id'];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch almacenes list for pickup location', ['error' => $e->getMessage()]);
        }

        throw new \Exception("No hay almacenes disponibles en plantaCruds para la ubicación de recojo: {$address}");
    }

    /**
     * Find existing almacen or return default
     * 
     * @param OrderDestination $destination
     * @return int
     * @throws \Exception
     */
    private function findOrCreateAlmacen(OrderDestination $destination): int
    {
        // First, try to get list of almacenes from plantaCruds
        try {
            $response = Http::timeout(10)->get("{$this->apiUrl}/almacenes");

            if ($response->successful()) {
                $almacenes = $response->json('data', []);

                // Try to find by coordinates if available
                if ($destination->latitude && $destination->longitude) {
                    foreach ($almacenes as $almacen) {
                        if (
                            isset($almacen['latitud']) && isset($almacen['longitud']) &&
                            $this->coordinatesMatch(
                                $almacen['latitud'],
                                $almacen['longitud'],
                                $destination->latitude,
                                $destination->longitude
                            )
                        ) {
                            Log::info('Found almacen by coordinates', [
                                'almacen_id' => $almacen['id'],
                                'almacen_nombre' => $almacen['nombre'],
                            ]);
                            return $almacen['id'];
                        }
                    }
                }

                // Try to find by address match
                foreach ($almacenes as $almacen) {
                    $almacenAddress = $almacen['direccion_completa'] ?? $almacen['nombre'] ?? '';
                    if (
                        stripos($almacenAddress, $destination->address) !== false ||
                        stripos($destination->address, $almacenAddress) !== false
                    ) {
                        Log::info('Found almacen by address match', [
                            'almacen_id' => $almacen['id'],
                            'almacen_nombre' => $almacen['nombre'],
                        ]);
                        return $almacen['id'];
                    }
                }

                // If no match found, use first active almacen that is NOT the plant (es_planta=false)
                foreach ($almacenes as $almacen) {
                    // Exclude plant from default destination
                    if (($almacen['activo'] ?? true) && !($almacen['es_planta'] ?? false)) {
                        Log::warning('No matching almacen found, using default (non-plant)', [
                            'almacen_id' => $almacen['id'],
                            'almacen_nombre' => $almacen['nombre'],
                            'destination_address' => $destination->address,
                        ]);
                        return $almacen['id'];
                    }
                }

                // If no active non-plant almacen, use first non-plant one
                foreach ($almacenes as $almacen) {
                    if (!($almacen['es_planta'] ?? false)) {
                        Log::warning('No active non-plant almacen found, using first non-plant', [
                            'almacen_id' => $almacen['id'],
                            'destination_address' => $destination->address,
                        ]);
                        return $almacen['id'];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch almacenes list', ['error' => $e->getMessage()]);
        }

        // If no almacen found at all, throw exception
        throw new \Exception("No hay almacenes disponibles en plantaCruds para el destino: {$destination->address}");
    }

    /**
     * Check if coordinates match within tolerance
     * 
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @param float $tolerance
     * @return bool
     */
    private function coordinatesMatch($lat1, $lng1, $lat2, $lng2, $tolerance = 0.001): bool
    {
        return abs($lat1 - $lat2) < $tolerance && abs($lng1 - $lng2) < $tolerance;
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
