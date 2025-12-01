<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TrazabilidadTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Units of Measure
        $units = [
            ['unit_of_measure_id' => 1, 'name' => 'Kilogram', 'abbreviation' => 'kg'],
            ['unit_of_measure_id' => 2, 'name' => 'Piece', 'abbreviation' => 'pcs'],
            ['unit_of_measure_id' => 3, 'name' => 'Liter', 'abbreviation' => 'L'],
            ['unit_of_measure_id' => 4, 'name' => 'Meter', 'abbreviation' => 'm'],
        ];

        foreach ($units as $unit) {
            DB::table('unit_of_measure')->insertOrIgnore($unit);
        }

        // Create Statuses
        $statuses = [
            ['status_id' => 1, 'name' => 'Available'],
            ['status_id' => 2, 'name' => 'In Progress'],
            ['status_id' => 3, 'name' => 'Completed'],
            ['status_id' => 4, 'name' => 'Low Stock'],
            ['status_id' => 5, 'name' => 'Out of Stock'],
            ['status_id' => 6, 'name' => 'Pending'],
        ];

        foreach ($statuses as $status) {
            DB::table('status')->insertOrIgnore($status);
        }

        // Create Operator Roles
        $roles = [
            ['operator_role_id' => 1, 'name' => 'Administrator'],
            ['operator_role_id' => 2, 'name' => 'Production Manager'],
            ['operator_role_id' => 3, 'name' => 'Machine Operator'],
            ['operator_role_id' => 4, 'name' => 'Quality Inspector'],
        ];

        foreach ($roles as $role) {
            DB::table('operator_role')->insertOrIgnore($role);
        }

        // Create Operators (Users)
        $operators = [
            [
                'operator_id' => 1,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'admin',
                'email' => 'admin@trazabilidad.com',
                'password' => Hash::make('password'),
                'operator_role_id' => 1,
            ],
            [
                'operator_id' => 2,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'username' => 'manager',
                'email' => 'manager@trazabilidad.com',
                'password' => Hash::make('password'),
                'operator_role_id' => 2,
            ],
        ];

        foreach ($operators as $operator) {
            DB::table('operator')->insertOrIgnore($operator);
        }

        // Create Raw Material Categories
        $categories = [
            ['raw_material_category_id' => 1, 'name' => 'Metals'],
            ['raw_material_category_id' => 2, 'name' => 'Polymers'],
            ['raw_material_category_id' => 3, 'name' => 'Chemicals'],
        ];

        foreach ($categories as $category) {
            DB::table('raw_material_category')->insertOrIgnore($category);
        }

        // Create Raw Material Bases
        $materialBases = [
            [
                'raw_material_base_id' => 1,
                'name' => 'Steel Plate',
                'description' => 'High quality steel plates for manufacturing',
                'raw_material_category_id' => 1,
            ],
            [
                'raw_material_base_id' => 2,
                'name' => 'Aluminum Rod',
                'description' => 'Aluminum rods for precision components',
                'raw_material_category_id' => 1,
            ],
            [
                'raw_material_base_id' => 3,
                'name' => 'Plastic Pellets',
                'description' => 'High-grade plastic pellets',
                'raw_material_category_id' => 2,
            ],
        ];

        foreach ($materialBases as $base) {
            DB::table('raw_material_base')->insertOrIgnore($base);
        }

        // Create Suppliers
        $suppliers = [
            [
                'supplier_id' => 1,
                'name' => 'Steel Works Inc',
                'contact_person' => 'Mike Johnson',
                'email' => 'mike@steelworks.com',
                'phone' => '555-0101',
                'address' => '123 Industrial Ave',
            ],
            [
                'supplier_id' => 2,
                'name' => 'Aluminum Solutions',
                'contact_person' => 'Sara Wilson',
                'email' => 'sara@aluminumsol.com',
                'phone' => '555-0102',
                'address' => '456 Metal Street',
            ],
        ];

        foreach ($suppliers as $supplier) {
            DB::table('supplier')->insertOrIgnore($supplier);
        }

        // Create Raw Materials
        $materials = [
            [
                'raw_material_id' => 1,
                'raw_material_base_id' => 1,
                'supplier_id' => 1,
                'lot_number' => 'ST-2024-001',
                'quantity' => 500,
                'unit_of_measure_id' => 1,
                'status_id' => 1,
                'expiration_date' => '2025-12-31',
            ],
            [
                'raw_material_id' => 2,
                'raw_material_base_id' => 2,
                'supplier_id' => 2,
                'lot_number' => 'AL-2024-001',
                'quantity' => 250,
                'unit_of_measure_id' => 2,
                'status_id' => 4,
                'expiration_date' => '2025-06-30',
            ],
            [
                'raw_material_id' => 3,
                'raw_material_base_id' => 3,
                'supplier_id' => 1,
                'lot_number' => 'PL-2024-001',
                'quantity' => 1000,
                'unit_of_measure_id' => 1,
                'status_id' => 1,
            ],
        ];

        foreach ($materials as $material) {
            DB::table('raw_material')->insertOrIgnore($material);
        }

        // Create Customers
        $customers = [
            [
                'customer_id' => 1,
                'name' => 'ABC Manufacturing',
                'email' => 'orders@abcmanuf.com',
                'phone' => '555-2001',
                'address' => '789 Factory Road',
            ],
            [
                'customer_id' => 2,
                'name' => 'XYZ Industries',
                'email' => 'procurement@xyzind.com',
                'phone' => '555-2002',
                'address' => '321 Business Blvd',
            ],
        ];

        foreach ($customers as $customer) {
            DB::table('customer')->insertOrIgnore($customer);
        }

        // Create Customer Orders
        $orders = [
            [
                'customer_order_id' => 1,
                'customer_id' => 1,
                'product_name' => 'Steel Components',
                'quantity' => 100,
                'unit_of_measure_id' => 2,
                'status_id' => 2,
                'requested_delivery_date' => '2024-02-15',
                'notes' => 'High priority order',
            ],
            [
                'customer_order_id' => 2,
                'customer_id' => 2,
                'product_name' => 'Aluminum Parts',
                'quantity' => 75,
                'unit_of_measure_id' => 2,
                'status_id' => 6,
                'requested_delivery_date' => '2024-02-20',
            ],
        ];

        foreach ($orders as $order) {
            DB::table('customer_order')->insertOrIgnore($order);
        }

        // Create Processes
        $processes = [
            [
                'process_id' => 1,
                'name' => 'Cutting and Shaping',
                'description' => 'Initial cutting and shaping of raw materials',
            ],
            [
                'process_id' => 2,
                'name' => 'Assembly',
                'description' => 'Assembly of components into final products',
            ],
        ];

        foreach ($processes as $process) {
            DB::table('process')->insertOrIgnore($process);
        }

        // Create Production Batches
        $batches = [
            [
                'production_batch_id' => 1,
                'customer_order_id' => 1,
                'process_id' => 1,
                'operator_id' => 2,
                'status_id' => 2,
                'start_date' => '2024-01-15',
                'expected_quantity' => 100,
                'actual_quantity' => 95,
                'notes' => 'Production in progress',
            ],
            [
                'production_batch_id' => 2,
                'customer_order_id' => 2,
                'process_id' => 1,
                'operator_id' => 2,
                'status_id' => 6,
                'expected_quantity' => 75,
                'notes' => 'Waiting for raw materials',
            ],
        ];

        foreach ($batches as $batch) {
            DB::table('production_batch')->insertOrIgnore($batch);
        }

        $this->command->info('Test data seeded successfully!');
    }
}