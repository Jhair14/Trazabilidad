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
        // Seeders de tablas de parametrización (en orden de dependencias)
        $this->call([
            UnitOfMeasureSeeder::class,
            StatusSeeder::class,
            MovementTypeSeeder::class,
            OperatorRoleSeeder::class,
            OperatorSeeder::class,
            RawMaterialCategorySeeder::class,
            StandardVariableSeeder::class,
            MachineSeeder::class,
            ProcessSeeder::class,
        ]);

        // Usuario de prueba (opcional)
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
