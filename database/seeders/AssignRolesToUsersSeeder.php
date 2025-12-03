<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Operator;
use Spatie\Permission\Models\Role;

class AssignRolesToUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener roles
        $adminRole = Role::findByName('admin');
        $operadorRole = Role::findByName('operador');
        $clienteRole = Role::findByName('cliente');

        // Asignar roles según el role_id del operador
        $operators = Operator::where('active', true)->get();
        
        foreach ($operators as $operator) {
            // Si el operador ya tiene roles, no hacer nada
            if ($operator->roles->count() > 0) {
                continue;
            }

            // Determinar el rol basado en el role_id
            if ($operator->role_id) {
                $roleCode = \DB::table('operator_role')->where('role_id', $operator->role_id)->value('code');
                
                if ($roleCode === 'ADMIN') {
                    $operator->assignRole($adminRole);
                } elseif ($roleCode === 'OPERADOR') {
                    $operator->assignRole($operadorRole);
                } elseif ($roleCode === 'CLIENTE') {
                    $operator->assignRole($clienteRole);
                } else {
                    // Si no coincide con ningún código conocido, asignar cliente por defecto
                    $operator->assignRole($clienteRole);
                }
            } else {
                // Si no tiene role_id, asignar cliente por defecto
                $operator->assignRole($clienteRole);
            }
        }

        $this->command->info('Roles asignados a operadores exitosamente.');
    }
}

