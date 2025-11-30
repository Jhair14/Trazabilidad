<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            // Estados para Órdenes de Cliente
            [
                'status_id' => 1,
                'entity_type' => 'customer_order',
                'code' => 'PENDING',
                'name' => 'Pendiente',
                'description' => 'Orden creada y pendiente de procesamiento',
                'sort_order' => 1,
                'active' => true,
            ],
            [
                'status_id' => 2,
                'entity_type' => 'customer_order',
                'code' => 'IN_PROGRESS',
                'name' => 'En Proceso',
                'description' => 'Orden en proceso de producción',
                'sort_order' => 2,
                'active' => true,
            ],
            [
                'status_id' => 3,
                'entity_type' => 'customer_order',
                'code' => 'COMPLETED',
                'name' => 'Completada',
                'description' => 'Orden completada y lista para entrega',
                'sort_order' => 3,
                'active' => true,
            ],
            [
                'status_id' => 4,
                'entity_type' => 'customer_order',
                'code' => 'DELIVERED',
                'name' => 'Entregada',
                'description' => 'Orden entregada al cliente',
                'sort_order' => 4,
                'active' => true,
            ],
            [
                'status_id' => 5,
                'entity_type' => 'customer_order',
                'code' => 'CANCELLED',
                'name' => 'Cancelada',
                'description' => 'Orden cancelada',
                'sort_order' => 5,
                'active' => true,
            ],
            // Estados para Lotes de Producción
            [
                'status_id' => 10,
                'entity_type' => 'production_batch',
                'code' => 'CREATED',
                'name' => 'Creado',
                'description' => 'Lote creado y listo para iniciar',
                'sort_order' => 1,
                'active' => true,
            ],
            [
                'status_id' => 11,
                'entity_type' => 'production_batch',
                'code' => 'IN_PROGRESS',
                'name' => 'En Producción',
                'description' => 'Lote en proceso de producción',
                'sort_order' => 2,
                'active' => true,
            ],
            [
                'status_id' => 12,
                'entity_type' => 'production_batch',
                'code' => 'QUALITY_CHECK',
                'name' => 'Control de Calidad',
                'description' => 'Lote en proceso de control de calidad',
                'sort_order' => 3,
                'active' => true,
            ],
            [
                'status_id' => 13,
                'entity_type' => 'production_batch',
                'code' => 'APPROVED',
                'name' => 'Aprobado',
                'description' => 'Lote aprobado por control de calidad',
                'sort_order' => 4,
                'active' => true,
            ],
            [
                'status_id' => 14,
                'entity_type' => 'production_batch',
                'code' => 'REJECTED',
                'name' => 'Rechazado',
                'description' => 'Lote rechazado por control de calidad',
                'sort_order' => 5,
                'active' => true,
            ],
            [
                'status_id' => 15,
                'entity_type' => 'production_batch',
                'code' => 'STORED',
                'name' => 'Almacenado',
                'description' => 'Lote almacenado',
                'sort_order' => 6,
                'active' => true,
            ],
            // Estados para Solicitudes de Material
            [
                'status_id' => 20,
                'entity_type' => 'material_request',
                'code' => 'PENDING',
                'name' => 'Pendiente',
                'description' => 'Solicitud pendiente de aprobación',
                'sort_order' => 1,
                'active' => true,
            ],
            [
                'status_id' => 21,
                'entity_type' => 'material_request',
                'code' => 'APPROVED',
                'name' => 'Aprobada',
                'description' => 'Solicitud aprobada',
                'sort_order' => 2,
                'active' => true,
            ],
            [
                'status_id' => 22,
                'entity_type' => 'material_request',
                'code' => 'REJECTED',
                'name' => 'Rechazada',
                'description' => 'Solicitud rechazada',
                'sort_order' => 3,
                'active' => true,
            ],
            [
                'status_id' => 23,
                'entity_type' => 'material_request',
                'code' => 'FULFILLED',
                'name' => 'Completada',
                'description' => 'Solicitud completada',
                'sort_order' => 4,
                'active' => true,
            ],
        ];

        foreach ($statuses as $status) {
            DB::table('status')->updateOrInsert(
                ['status_id' => $status['status_id']],
                $status
            );
        }
    }
}
