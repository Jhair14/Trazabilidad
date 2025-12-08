<?php

namespace Database\Seeders;

use App\Models\Operator;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class OperatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $operators = [
            [
                'operator_id' => 1,
                'role_id' => 1, // ADMIN
                'first_name' => 'jhair',
                'last_name' => 'aguilar',
                'username' => 'jhair',
                'password_hash' => '$2y$12$PsWcWtGV3nuBopkEusDKFup5.T5/FrHW0jeUV2ElAjeRJMa7Jgczq',
                'email' => 'jhair@gmail.com',
                'active' => true,
            ],
            [
                'operator_id' => 2,
                'role_id' => 1, // ADMIN
                'first_name' => 'Admin',
                'last_name' => 'User',
                'username' => 'admin',
                'password_hash' => '$2y$12$R5QvNQItfWqSalSFzAoyGeUHA9lAwGyfpw50IgsleDgibPNWFNOby',
                'email' => 'admin@admin.com',
                'active' => true,
            ],
        ];

        foreach ($operators as $operatorData) {
            $operator = Operator::updateOrCreate(
                ['operator_id' => $operatorData['operator_id']],
                $operatorData
            );

            // Asignar rol de Spatie basado en el role_id
            $roleId = $operatorData['role_id'];
            $spatieRole = null;

            if ($roleId == 1) {
                // ADMIN
                $spatieRole = Role::where('name', 'admin')->first();
            } elseif ($roleId == 2) {
                // OPERADOR
                $spatieRole = Role::where('name', 'operador')->first();
            } elseif ($roleId == 3) {
                // CLIENTE
                $spatieRole = Role::where('name', 'cliente')->first();
            }

            // Asignar el rol de Spatie si existe
            if ($spatieRole) {
                // Remover todos los roles existentes y asignar el correcto
                $operator->syncRoles([$spatieRole]);
            }
        }
    }
}

