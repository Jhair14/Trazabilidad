<?php

namespace Database\Seeders;

use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitOfMeasureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'unit_id' => 1,
                'code' => 'KG',
                'name' => 'Kilogramo',
                'description' => 'Unidad de masa en el Sistema Internacional',
                'active' => true,
            ],
            [
                'unit_id' => 2,
                'code' => 'G',
                'name' => 'Gramo',
                'description' => 'Unidad de masa equivalente a una milésima de kilogramo',
                'active' => true,
            ],
            [
                'unit_id' => 3,
                'code' => 'L',
                'name' => 'Litro',
                'description' => 'Unidad de volumen en el Sistema Internacional',
                'active' => true,
            ],
            [
                'unit_id' => 4,
                'code' => 'ML',
                'name' => 'Mililitro',
                'description' => 'Unidad de volumen equivalente a una milésima de litro',
                'active' => true,
            ],
            [
                'unit_id' => 5,
                'code' => 'M',
                'name' => 'Metro',
                'description' => 'Unidad de longitud en el Sistema Internacional',
                'active' => true,
            ],
            [
                'unit_id' => 6,
                'code' => 'CM',
                'name' => 'Centímetro',
                'description' => 'Unidad de longitud equivalente a una centésima de metro',
                'active' => true,
            ],
            [
                'unit_id' => 7,
                'code' => 'UN',
                'name' => 'Unidad',
                'description' => 'Unidad de conteo o pieza',
                'active' => true,
            ],
            [
                'unit_id' => 8,
                'code' => 'M2',
                'name' => 'Metro Cuadrado',
                'description' => 'Unidad de área',
                'active' => true,
            ],
            [
                'unit_id' => 9,
                'code' => 'M3',
                'name' => 'Metro Cúbico',
                'description' => 'Unidad de volumen',
                'active' => true,
            ],
            [
                'unit_id' => 10,
                'code' => 'BOLSA',
                'name' => 'Bolsa',
                'description' => 'Unidad de empaque en bolsa',
                'active' => true,
            ],
        ];

        foreach ($units as $unit) {
            UnitOfMeasure::updateOrCreate(
                ['unit_id' => $unit['unit_id']],
                $unit
            );
        }
    }
}
