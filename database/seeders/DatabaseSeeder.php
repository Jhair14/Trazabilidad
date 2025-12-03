<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Primero crear roles y permisos de Spatie
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // Seeders de tablas de parametrización (en orden de dependencias)
        $this->call([
            UnitOfMeasureSeeder::class,
            StatusSeeder::class,
            MovementTypeSeeder::class,
            OperatorRoleSeeder::class,
            OperatorSeeder::class,
            AssignRolesToUsersSeeder::class, // Asignar roles de Spatie a operadores
            RawMaterialCategorySeeder::class,
            StandardVariableSeeder::class,
            MachineSeeder::class,
            ProcessSeeder::class,
            UpdateProductsAndRolesSeeder::class, // Crear productos
        ]);

        // Usuario de prueba (opcional)
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
