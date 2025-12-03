<?php

namespace Database\Seeders;

use App\Models\OperatorRole;
use Illuminate\Database\Seeder;

class OperatorRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'role_id' => 1,
                'code' => 'ADMIN',
                'name' => 'Administrador',
                'description' => 'Acceso completo al sistema',
                'access_level' => 10,
                'active' => true,
            ],
            [
                'role_id' => 2,
                'code' => 'OPERADOR',
                'name' => 'Operador',
                'description' => 'Opera máquinas y registra datos de producción',
                'access_level' => 5,
                'active' => true,
            ],
            [
                'role_id' => 3,
                'code' => 'CLIENTE',
                'name' => 'Cliente',
                'description' => 'Acceso para gestionar pedidos y ver certificados',
                'access_level' => 3,
                'active' => true,
            ],
        ];

        foreach ($roles as $role) {
            // Primero buscar por código para evitar duplicados
            $existing = OperatorRole::where('code', $role['code'])->first();
            
            if ($existing) {
                // Si existe con diferente role_id, actualizar el existente
                if ($existing->role_id !== $role['role_id']) {
                    // Eliminar el registro con role_id diferente si existe
                    OperatorRole::where('role_id', $role['role_id'])->delete();
                    $existing->update($role);
                } else {
                    // Actualizar el existente
                    $existing->update($role);
                }
            } else {
                // Si no existe, crear o actualizar por role_id
                OperatorRole::updateOrCreate(
                    ['role_id' => $role['role_id']],
                    $role
                );
            }
        }
    }
}
