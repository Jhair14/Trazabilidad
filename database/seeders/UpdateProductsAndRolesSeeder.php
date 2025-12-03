<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateProductsAndRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Actualizar roles existentes o crear si no existen
        $roles = [
            ['role_id' => 1, 'code' => 'ADMIN', 'name' => 'Administrador', 'description' => 'Administrador del sistema', 'access_level' => 5, 'active' => true],
            ['role_id' => 2, 'code' => 'OPERATOR', 'name' => 'Operador', 'description' => 'Operador de producción', 'access_level' => 3, 'active' => true],
            ['role_id' => 3, 'code' => 'CLIENT', 'name' => 'Cliente', 'description' => 'Cliente que realiza pedidos', 'access_level' => 1, 'active' => true],
        ];

        foreach ($roles as $role) {
            DB::table('operator_role')->updateOrInsert(
                ['role_id' => $role['role_id']],
                $role
            );
        }

        // Crear productos si no existen
        $products = [
            ['product_id' => 1, 'code' => 'CAFE-UNIVALLE-500G', 'name' => 'Café Univalle Orgánico 500 g', 'type' => 'marca_univalle', 'weight' => 0.5, 'unit_id' => 1, 'description' => 'Café orgánico marca Univalle', 'active' => true],
            ['product_id' => 2, 'code' => 'MIEL-UNIVALLE-350G', 'name' => 'Miel Univalle Pura 350 g', 'type' => 'marca_univalle', 'weight' => 0.35, 'unit_id' => 1, 'description' => 'Miel pura marca Univalle', 'active' => true],
            ['product_id' => 3, 'code' => 'GRANOLA-UNIVALLE-750G', 'name' => 'Granola Univalle Natural 750 g', 'type' => 'marca_univalle', 'weight' => 0.75, 'unit_id' => 1, 'description' => 'Granola natural marca Univalle', 'active' => true],
            ['product_id' => 4, 'code' => 'YOGUR-BIO-NATURAL-1L', 'name' => 'Yogur Univalle Bio Natural 1 L', 'type' => 'marca_univalle', 'weight' => 1.0, 'unit_id' => 3, 'description' => 'Yogur bio natural marca Univalle', 'active' => true],
            ['product_id' => 5, 'code' => 'YOGUR-BIO-FRUTILLA-1L', 'name' => 'Yogur Univalle Bio Frutilla 1 L', 'type' => 'marca_univalle', 'weight' => 1.0, 'unit_id' => 3, 'description' => 'Yogur bio frutilla marca Univalle', 'active' => true],
            ['product_id' => 6, 'code' => 'HARINA-INTEGRAL-1KG', 'name' => 'Harina Integral Univalle Vital 1 kg', 'type' => 'marca_univalle', 'weight' => 1.0, 'unit_id' => 1, 'description' => 'Harina integral marca Univalle', 'active' => true],
            ['product_id' => 7, 'code' => 'AVENA-ORGANICA-900G', 'name' => 'Avena Univalle Orgánica 900 g', 'type' => 'organico', 'weight' => 0.9, 'unit_id' => 1, 'description' => 'Avena orgánica marca Univalle', 'active' => true],
            ['product_id' => 8, 'code' => 'CHOCOLATE-AMARGO-100G', 'name' => 'Chocolate Amargo Univalle 70% 100 g', 'type' => 'marca_univalle', 'weight' => 0.1, 'unit_id' => 1, 'description' => 'Chocolate amargo 70% marca Univalle', 'active' => true],
            ['product_id' => 9, 'code' => 'QUINUA-REAL-1KG', 'name' => 'Quinua Real Univalle 1 kg', 'type' => 'marca_univalle', 'weight' => 1.0, 'unit_id' => 1, 'description' => 'Quinua real marca Univalle', 'active' => true],
            ['product_id' => 10, 'code' => 'ARROZ-INTEGRAL-1KG', 'name' => 'Arroz Integral Univalle 1 kg', 'type' => 'marca_univalle', 'weight' => 1.0, 'unit_id' => 1, 'description' => 'Arroz integral marca Univalle', 'active' => true],
            ['product_id' => 11, 'code' => 'ACEITE-COCO-300ML', 'name' => 'Aceite de Coco Univalle 300 ml', 'type' => 'marca_univalle', 'weight' => 0.3, 'unit_id' => 3, 'description' => 'Aceite de coco marca Univalle', 'active' => true],
            ['product_id' => 12, 'code' => 'PAN-INTEGRAL-600G', 'name' => 'Pan Integral Univalle 600 g', 'type' => 'marca_univalle', 'weight' => 0.6, 'unit_id' => 1, 'description' => 'Pan integral marca Univalle', 'active' => true],
            ['product_id' => 13, 'code' => 'FRUTOS-SECOS-MIX-250G', 'name' => 'Frutos Secos Univalle Mix 250 g', 'type' => 'marca_univalle', 'weight' => 0.25, 'unit_id' => 1, 'description' => 'Mix de frutos secos marca Univalle', 'active' => true],
            ['product_id' => 14, 'code' => 'GALLETAS-INTEGRALES-200G', 'name' => 'Galletas Integrales Univalle 200 g', 'type' => 'marca_univalle', 'weight' => 0.2, 'unit_id' => 1, 'description' => 'Galletas integrales marca Univalle', 'active' => true],
            ['product_id' => 15, 'code' => 'SIROPE-AGAVE-250ML', 'name' => 'Sirope de Agave Univalle 250 ml', 'type' => 'marca_univalle', 'weight' => 0.25, 'unit_id' => 3, 'description' => 'Sirope de agave marca Univalle', 'active' => true],
            ['product_id' => 16, 'code' => 'TE-VERDE-20SOBRES', 'name' => 'Té Verde Univalle Orgánico 20 sobres', 'type' => 'organico', 'weight' => 0.05, 'unit_id' => 2, 'description' => 'Té verde orgánico marca Univalle', 'active' => true],
            ['product_id' => 17, 'code' => 'MANTEQUILLA-MANI-350G', 'name' => 'Mantequilla de Maní Univalle 350 g', 'type' => 'marca_univalle', 'weight' => 0.35, 'unit_id' => 1, 'description' => 'Mantequilla de maní marca Univalle', 'active' => true],
            ['product_id' => 18, 'code' => 'LENTEJAS-ORGANICAS-900G', 'name' => 'Lentejas Univalle Orgánicas 900 g', 'type' => 'organico', 'weight' => 0.9, 'unit_id' => 1, 'description' => 'Lentejas orgánicas marca Univalle', 'active' => true],
            ['product_id' => 19, 'code' => 'CEREAL-MAIZ-500G', 'name' => 'Cereal de Maíz Univalle 500 g', 'type' => 'marca_univalle', 'weight' => 0.5, 'unit_id' => 1, 'description' => 'Cereal de maíz marca Univalle', 'active' => true],
            ['product_id' => 20, 'code' => 'PASTA-INTEGRAL-500G', 'name' => 'Pasta Integral Univalle 500 g', 'type' => 'marca_univalle', 'weight' => 0.5, 'unit_id' => 1, 'description' => 'Pasta integral marca Univalle', 'active' => true],
        ];

        foreach ($products as $product) {
            DB::table('product')->updateOrInsert(
                ['product_id' => $product['product_id']],
                $product
            );
        }

        $this->command->info('Productos y roles actualizados exitosamente.');
    }
}

