<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CorrectSchemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Units of Measure
        $units = [
            ['unit_id' => 1, 'code' => 'KG', 'name' => 'Kilogram', 'description' => 'Weight in kilograms', 'active' => true],
            ['unit_id' => 2, 'code' => 'PCS', 'name' => 'Pieces', 'description' => 'Count in pieces', 'active' => true],
            ['unit_id' => 3, 'code' => 'L', 'name' => 'Liter', 'description' => 'Volume in liters', 'active' => true],
            ['unit_id' => 4, 'code' => 'M', 'name' => 'Meter', 'description' => 'Length in meters', 'active' => true],
        ];

        foreach ($units as $unit) {
            DB::table('unit_of_measure')->insert($unit);
        }

        // Create Statuses
        $statuses = [
            ['status_id' => 1, 'entity_type' => 'material', 'code' => 'AVAILABLE', 'name' => 'Available', 'sort_order' => 1],
            ['status_id' => 2, 'entity_type' => 'batch', 'code' => 'IN_PROGRESS', 'name' => 'In Progress', 'sort_order' => 2],
            ['status_id' => 3, 'entity_type' => 'batch', 'code' => 'COMPLETED', 'name' => 'Completed', 'sort_order' => 3],
            ['status_id' => 4, 'entity_type' => 'material', 'code' => 'LOW_STOCK', 'name' => 'Low Stock', 'sort_order' => 4],
            ['status_id' => 5, 'entity_type' => 'material', 'code' => 'OUT_STOCK', 'name' => 'Out of Stock', 'sort_order' => 5],
            ['status_id' => 6, 'entity_type' => 'order', 'code' => 'PENDING', 'name' => 'Pending', 'sort_order' => 6],
        ];

        foreach ($statuses as $status) {
            DB::table('status')->insert($status);
        }

        // Create Operator Roles - Solo 3 roles: cliente, operador, admin
        $roles = [
            ['role_id' => 1, 'code' => 'ADMIN', 'name' => 'Administrador', 'description' => 'Administrador del sistema', 'access_level' => 10, 'active' => true],
            ['role_id' => 2, 'code' => 'OPERADOR', 'name' => 'Operador', 'description' => 'Operador de producción', 'access_level' => 5, 'active' => true],
            ['role_id' => 3, 'code' => 'CLIENTE', 'name' => 'Cliente', 'description' => 'Cliente que realiza pedidos', 'access_level' => 3, 'active' => true],
        ];

        foreach ($roles as $role) {
            DB::table('operator_role')->insert($role);
        }

        // Create Operators (Users)
        $operators = [
            [
                'operator_id' => 1,
                'role_id' => 1,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'admin',
                'password_hash' => Hash::make('password'),
                'email' => 'admin@trazabilidad.com',
            ],
            [
                'operator_id' => 2,
                'role_id' => 2,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'username' => 'manager',
                'password_hash' => Hash::make('password'),
                'email' => 'manager@trazabilidad.com',
            ],
        ];

        foreach ($operators as $operator) {
            DB::table('operator')->insert($operator);
        }

        // Create Raw Material Categories
        $categories = [
            ['category_id' => 1, 'code' => 'METALS', 'name' => 'Metals', 'description' => 'Metal materials'],
            ['category_id' => 2, 'code' => 'POLYMERS', 'name' => 'Polymers', 'description' => 'Polymer materials'],
            ['category_id' => 3, 'code' => 'CHEMICALS', 'name' => 'Chemicals', 'description' => 'Chemical materials'],
        ];

        foreach ($categories as $category) {
            DB::table('raw_material_category')->insert($category);
        }

        // Create Products
        $products = [
            [
                'product_id' => 1,
                'code' => 'PROD-ORG-001',
                'name' => 'Aceite Orgánico Premium',
                'type' => 'organico',
                'weight' => 0.5,
                'unit_id' => 1, // KG
                'description' => 'Aceite orgánico de alta calidad',
                'active' => true,
            ],
            [
                'product_id' => 2,
                'code' => 'PROD-UNIVALLE-001',
                'name' => 'Harina Univalle',
                'type' => 'marca_univalle',
                'weight' => 1.0,
                'unit_id' => 1, // KG
                'description' => 'Harina marca Univalle',
                'active' => true,
            ],
            [
                'product_id' => 3,
                'code' => 'PROD-COMEST-001',
                'name' => 'Arroz Premium',
                'type' => 'comestibles',
                'weight' => 2.5,
                'unit_id' => 1, // KG
                'description' => 'Arroz de alta calidad',
                'active' => true,
            ],
            [
                'product_id' => 4,
                'code' => 'PROD-ORG-002',
                'name' => 'Miel Orgánica',
                'type' => 'organico',
                'weight' => 0.25,
                'unit_id' => 1, // KG
                'description' => 'Miel 100% orgánica',
                'active' => true,
            ],
            [
                'product_id' => 5,
                'code' => 'PROD-UNIVALLE-002',
                'name' => 'Azúcar Univalle',
                'type' => 'marca_univalle',
                'weight' => 1.0,
                'unit_id' => 1, // KG
                'description' => 'Azúcar marca Univalle',
                'active' => true,
            ],
        ];

        foreach ($products as $product) {
            DB::table('product')->insert($product);
        }

        // Create Raw Material Bases
        $materialBases = [
            [
                'material_id' => 1,
                'category_id' => 1,
                'unit_id' => 1,
                'code' => 'STEEL_PLATE',
                'name' => 'Steel Plate',
                'description' => 'High quality steel plates for manufacturing',
                'available_quantity' => 500.0,
                'minimum_stock' => 100.0,
                'maximum_stock' => 1000.0,
            ],
            [
                'material_id' => 2,
                'category_id' => 1,
                'unit_id' => 2,
                'code' => 'ALU_ROD',
                'name' => 'Aluminum Rod',
                'description' => 'Aluminum rods for precision components',
                'available_quantity' => 250.0,
                'minimum_stock' => 50.0,
                'maximum_stock' => 500.0,
            ],
            [
                'material_id' => 3,
                'category_id' => 2,
                'unit_id' => 1,
                'code' => 'PLASTIC_PELLETS',
                'name' => 'Plastic Pellets',
                'description' => 'High-grade plastic pellets',
                'available_quantity' => 1000.0,
                'minimum_stock' => 200.0,
                'maximum_stock' => 2000.0,
            ],
        ];

        foreach ($materialBases as $base) {
            DB::table('raw_material_base')->insert($base);
        }

        // Create Suppliers
        $suppliers = [
            [
                'supplier_id' => 1,
                'business_name' => 'Steel Works Inc',
                'trading_name' => 'SteelWorks',
                'contact_person' => 'Mike Johnson',
                'email' => 'mike@steelworks.com',
                'phone' => '555-0101',
                'address' => '123 Industrial Ave',
            ],
            [
                'supplier_id' => 2,
                'business_name' => 'Aluminum Solutions Ltd',
                'trading_name' => 'AlumSol',
                'contact_person' => 'Sara Wilson',
                'email' => 'sara@aluminumsol.com',
                'phone' => '555-0102',
                'address' => '456 Metal Street',
            ],
        ];

        foreach ($suppliers as $supplier) {
            DB::table('supplier')->insert($supplier);
        }

        // Create Raw Materials
        $materials = [
            [
                'raw_material_id' => 1,
                'material_id' => 1,
                'supplier_id' => 1,
                'supplier_batch' => 'ST-2024-001',
                'receipt_date' => '2024-01-10',
                'quantity' => 500.0,
                'available_quantity' => 500.0,
                'expiration_date' => '2025-12-31',
                'receipt_conformity' => true,
            ],
            [
                'raw_material_id' => 2,
                'material_id' => 2,
                'supplier_id' => 2,
                'supplier_batch' => 'AL-2024-001',
                'receipt_date' => '2024-01-12',
                'quantity' => 250.0,
                'available_quantity' => 50.0, // Low stock
                'expiration_date' => '2025-06-30',
                'receipt_conformity' => true,
            ],
            [
                'raw_material_id' => 3,
                'material_id' => 3,
                'supplier_id' => 1,
                'supplier_batch' => 'PL-2024-001',
                'receipt_date' => '2024-01-15',
                'quantity' => 1000.0,
                'available_quantity' => 1000.0,
                'receipt_conformity' => true,
            ],
        ];

        foreach ($materials as $material) {
            DB::table('raw_material')->insert($material);
        }

        // Create Customers
        $customers = [
            [
                'customer_id' => 1,
                'business_name' => 'ABC Manufacturing Corp',
                'trading_name' => 'ABC Manufacturing',
                'tax_id' => '12345678901',
                'email' => 'orders@abcmanuf.com',
                'phone' => '555-2001',
                'address' => '789 Factory Road',
                'contact_person' => 'Robert Brown',
            ],
            [
                'customer_id' => 2,
                'business_name' => 'XYZ Industries Ltd',
                'trading_name' => 'XYZ Industries',
                'tax_id' => '98765432109',
                'email' => 'procurement@xyzind.com',
                'phone' => '555-2002',
                'address' => '321 Business Blvd',
                'contact_person' => 'Lisa Davis',
            ],
        ];

        foreach ($customers as $customer) {
            DB::table('customer')->insert($customer);
        }

        // Create Customer Orders
        $orders = [
            [
                'order_id' => 1,
                'customer_id' => 1,
                'order_number' => 'ORD-2024-001',
                'creation_date' => '2024-01-15',
                'delivery_date' => '2024-02-15',
                'priority' => 1,
                'description' => 'Steel components for automotive industry',
                'observations' => 'High priority order',
            ],
            [
                'order_id' => 2,
                'customer_id' => 2,
                'order_number' => 'ORD-2024-002',
                'creation_date' => '2024-01-18',
                'delivery_date' => '2024-02-20',
                'priority' => 2,
                'description' => 'Aluminum parts for aerospace',
                'observations' => 'Quality critical',
            ],
        ];

        foreach ($orders as $order) {
            DB::table('customer_order')->insert($order);
        }

        // Create Processes
        $processes = [
            [
                'process_id' => 1,
                'code' => 'CUT_SHAPE',
                'name' => 'Cutting and Shaping',
                'description' => 'Initial cutting and shaping of raw materials',
            ],
            [
                'process_id' => 2,
                'code' => 'ASSEMBLY',
                'name' => 'Assembly',
                'description' => 'Assembly of components into final products',
            ],
        ];

        foreach ($processes as $process) {
            DB::table('process')->insert($process);
        }

        // Create Production Batches
        $batches = [
            [
                'batch_id' => 1,
                'order_id' => 1,
                'batch_code' => 'BATCH-2024-001',
                'name' => 'Steel Components Batch 1',
                'creation_date' => '2024-01-20',
                'start_time' => '2024-01-20 08:00:00',
                'target_quantity' => 100.0,
                'produced_quantity' => 95.0,
                'observations' => 'Production in progress',
            ],
            [
                'batch_id' => 2,
                'order_id' => 2,
                'batch_code' => 'BATCH-2024-002',
                'name' => 'Aluminum Parts Batch 1',
                'creation_date' => '2024-01-22',
                'target_quantity' => 75.0,
                'observations' => 'Waiting for raw materials',
            ],
        ];

        foreach ($batches as $batch) {
            DB::table('production_batch')->insert($batch);
        }

        $this->command->info('Test data seeded successfully with correct schema!');
    }
}