<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\OrderEnvioTracking;

class OrderEnvioTrackingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si hay pedidos y destinos en la base de datos
        $orders = DB::table('customer_order')->pluck('order_id')->toArray();
        $destinations = DB::table('order_destination')->pluck('destination_id')->toArray();

        if (empty($orders) || empty($destinations)) {
            $this->command->warn('No hay pedidos o destinos en la base de datos. Los seeders de tracking requieren que existan pedidos y destinos primero.');
            $this->command->info('Ejecuta primero los seeders de pedidos y destinos.');
            return;
        }

        // Datos de ejemplo para tracking
        $trackings = [
            [
                'order_id' => $orders[0] ?? 1,
                'destination_id' => $destinations[0] ?? 1,
                'envio_id' => 1001,
                'envio_codigo' => 'ENV-' . date('ymd') . '-000001',
                'status' => 'success',
                'error_message' => null,
                'request_data' => json_encode([
                    'almacen_destino_id' => 1,
                    'categoria' => 'general',
                    'fecha_estimada_entrega' => date('Y-m-d', strtotime('+7 days')),
                ]),
                'response_data' => json_encode([
                    'envio_id' => 1001,
                    'codigo' => 'ENV-' . date('ymd') . '-000001',
                    'status' => 'created',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $orders[0] ?? 1,
                'destination_id' => isset($destinations[1]) ? $destinations[1] : $destinations[0],
                'envio_id' => 1002,
                'envio_codigo' => 'ENV-' . date('ymd') . '-000002',
                'status' => 'success',
                'error_message' => null,
                'request_data' => json_encode([
                    'almacen_destino_id' => 2,
                    'categoria' => 'urgente',
                    'fecha_estimada_entrega' => date('Y-m-d', strtotime('+3 days')),
                ]),
                'response_data' => json_encode([
                    'envio_id' => 1002,
                    'codigo' => 'ENV-' . date('ymd') . '-000002',
                    'status' => 'created',
                ]),
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'order_id' => isset($orders[1]) ? $orders[1] : $orders[0],
                'destination_id' => $destinations[0] ?? 1,
                'envio_id' => null,
                'envio_codigo' => null,
                'status' => 'pending',
                'error_message' => null,
                'request_data' => json_encode([
                    'almacen_destino_id' => 1,
                    'categoria' => 'general',
                ]),
                'response_data' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => isset($orders[1]) ? $orders[1] : $orders[0],
                'destination_id' => isset($destinations[1]) ? $destinations[1] : $destinations[0],
                'envio_id' => null,
                'envio_codigo' => null,
                'status' => 'failed',
                'error_message' => 'Error al conectar con el servicio de envíos',
                'request_data' => json_encode([
                    'almacen_destino_id' => 2,
                    'categoria' => 'general',
                ]),
                'response_data' => json_encode([
                    'error' => 'Connection timeout',
                    'message' => 'No se pudo conectar con el servicio',
                ]),
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
        ];

        foreach ($trackings as $tracking) {
            // Verificar que el order_id y destination_id existen
            $orderExists = DB::table('customer_order')->where('order_id', $tracking['order_id'])->exists();
            $destinationExists = DB::table('order_destination')->where('destination_id', $tracking['destination_id'])->exists();

            if ($orderExists && $destinationExists) {
                OrderEnvioTracking::updateOrCreate(
                    [
                        'order_id' => $tracking['order_id'],
                        'destination_id' => $tracking['destination_id'],
                        'envio_codigo' => $tracking['envio_codigo'],
                    ],
                    $tracking
                );
            }
        }

        $this->command->info('Seeders de tracking cargados exitosamente!');
    }
}
