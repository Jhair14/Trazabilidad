<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RawMaterialCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'code' => 'CAT-GRANOS',
                'name' => 'Granos y Cereales',
                'description' => 'Trigo, maíz, arroz, avena, cebada, etc.',
                'active' => true
            ],
            [
                'code' => 'CAT-LACTEOS',
                'name' => 'Lácteos y Derivados',
                'description' => 'Leche, queso, mantequilla, yogurt, suero, etc.',
                'active' => true
            ],
            [
                'code' => 'CAT-CARNICOS',
                'name' => 'Cárnicos y Embutidos',
                'description' => 'Carne de res, cerdo, pollo, pescado, embutidos.',
                'active' => true
            ],
            [
                'code' => 'CAT-FRUTAS',
                'name' => 'Frutas y Verduras',
                'description' => 'Frutas frescas, verduras, hortalizas, tubérculos.',
                'active' => true
            ],
            [
                'code' => 'CAT-ACEITES',
                'name' => 'Aceites y Grasas',
                'description' => 'Aceite vegetal, manteca, margarina, grasas animales.',
                'active' => true
            ],
            [
                'code' => 'CAT-ESPECIAS',
                'name' => 'Especias y Condimentos',
                'description' => 'Sal, pimienta, orégano, comino, salsas, vinagres.',
                'active' => true
            ],
            [
                'code' => 'CAT-AZUCARES',
                'name' => 'Azúcares y Edulcorantes',
                'description' => 'Azúcar blanca, morena, miel, jarabes, edulcorantes.',
                'active' => true
            ],
            [
                'code' => 'CAT-ADITIVOS',
                'name' => 'Aditivos Alimentarios',
                'description' => 'Conservantes, colorantes, saborizantes, estabilizantes.',
                'active' => true
            ],
            [
                'code' => 'CAT-EMPAQUES',
                'name' => 'Empaques y Embalajes',
                'description' => 'Bolsas, cajas, etiquetas, frascos, tapas.',
                'active' => true
            ],
            [
                'code' => 'CAT-OTROS',
                'name' => 'Otros Insumos',
                'description' => 'Insumos que no encajan en las categorías anteriores.',
                'active' => true
            ],
        ];

        foreach ($categories as $category) {
            $existing = DB::table('raw_material_category')
                ->where('code', $category['code'])
                ->first();

            if ($existing) {
                DB::table('raw_material_category')
                    ->where('category_id', $existing->category_id)
                    ->update($category);
            } else {
                $maxId = DB::table('raw_material_category')->max('category_id') ?? 0;
                $category['category_id'] = $maxId + 1;
                DB::table('raw_material_category')->insert($category);
            }
        }
    }
}
