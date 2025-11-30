<?php

namespace Database\Seeders;

use App\Models\MovementType;
use Illuminate\Database\Seeder;

class MovementTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $movementTypes = [
            [
                'movement_type_id' => 1,
                'code' => 'ENTRADA',
                'name' => 'Entrada de Material',
                'affects_stock' => true,
                'is_entry' => true,
                'active' => true,
            ],
            [
                'movement_type_id' => 2,
                'code' => 'SALIDA',
                'name' => 'Salida de Material',
                'affects_stock' => true,
                'is_entry' => false,
                'active' => true,
            ],
            [
                'movement_type_id' => 3,
                'code' => 'AJUSTE_INV',
                'name' => 'Ajuste de Inventario',
                'affects_stock' => true,
                'is_entry' => false,
                'active' => true,
            ],
            [
                'movement_type_id' => 4,
                'code' => 'CONSUMO',
                'name' => 'Consumo en Producción',
                'affects_stock' => true,
                'is_entry' => false,
                'active' => true,
            ],
            [
                'movement_type_id' => 5,
                'code' => 'DEVOLUCION',
                'name' => 'Devolución de Material',
                'affects_stock' => true,
                'is_entry' => true,
                'active' => true,
            ],
            [
                'movement_type_id' => 6,
                'code' => 'PERDIDA',
                'name' => 'Pérdida de Material',
                'affects_stock' => true,
                'is_entry' => false,
                'active' => true,
            ],
            [
                'movement_type_id' => 7,
                'code' => 'TRANSFERENCIA',
                'name' => 'Transferencia entre Almacenes',
                'affects_stock' => false,
                'is_entry' => false,
                'active' => true,
            ],
            [
                'movement_type_id' => 8,
                'code' => 'VENCIMIENTO',
                'name' => 'Material Vencido',
                'affects_stock' => true,
                'is_entry' => false,
                'active' => true,
            ],
        ];

        foreach ($movementTypes as $movementType) {
            MovementType::updateOrCreate(
                ['movement_type_id' => $movementType['movement_type_id']],
                $movementType
            );
        }
    }
}
