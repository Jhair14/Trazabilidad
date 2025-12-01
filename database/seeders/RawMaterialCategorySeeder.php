<?php

namespace Database\Seeders;

use App\Models\RawMaterialCategory;
use Illuminate\Database\Seeder;

class RawMaterialCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'category_id' => 1,
                'code' => 'MAT_PRIMA',
                'name' => 'Materia Prima',
                'description' => 'Materiales básicos utilizados en la producción',
                'active' => true,
            ],
            [
                'category_id' => 2,
                'code' => 'INSUMOS',
                'name' => 'Insumos',
                'description' => 'Materiales auxiliares para el proceso productivo',
                'active' => true,
            ],
            [
                'category_id' => 3,
                'code' => 'EMPAQUE',
                'name' => 'Material de Empaque',
                'description' => 'Materiales para empaque y embalaje',
                'active' => true,
            ],
            [
                'category_id' => 4,
                'code' => 'QUIMICOS',
                'name' => 'Productos Químicos',
                'description' => 'Sustancias químicas utilizadas en procesos',
                'active' => true,
            ],
            [
                'category_id' => 5,
                'code' => 'ADITIVOS',
                'name' => 'Aditivos',
                'description' => 'Aditivos y conservantes',
                'active' => true,
            ],
            [
                'category_id' => 6,
                'code' => 'LUBRICANTES',
                'name' => 'Lubricantes',
                'description' => 'Aceites y lubricantes para maquinaria',
                'active' => true,
            ],
            [
                'category_id' => 7,
                'code' => 'REPUESTOS',
                'name' => 'Repuestos',
                'description' => 'Repuestos y componentes para maquinaria',
                'active' => true,
            ],
            [
                'category_id' => 8,
                'code' => 'OTROS',
                'name' => 'Otros',
                'description' => 'Otras categorías de materiales',
                'active' => true,
            ],
        ];

        foreach ($categories as $category) {
            RawMaterialCategory::updateOrCreate(
                ['category_id' => $category['category_id']],
                $category
            );
        }
    }
}
