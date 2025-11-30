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
                'code' => 'SUPERVISOR',
                'name' => 'Supervisor de Producción',
                'description' => 'Supervisa procesos de producción y control de calidad',
                'access_level' => 8,
                'active' => true,
            ],
            [
                'role_id' => 3,
                'code' => 'OPERADOR',
                'name' => 'Operador de Máquina',
                'description' => 'Opera máquinas y registra datos de producción',
                'access_level' => 5,
                'active' => true,
            ],
            [
                'role_id' => 4,
                'code' => 'CALIDAD',
                'name' => 'Inspector de Calidad',
                'description' => 'Realiza inspecciones y controles de calidad',
                'access_level' => 7,
                'active' => true,
            ],
            [
                'role_id' => 5,
                'code' => 'ALMACEN',
                'name' => 'Operador de Almacén',
                'description' => 'Gestiona inventario y movimientos de materiales',
                'access_level' => 6,
                'active' => true,
            ],
            [
                'role_id' => 6,
                'code' => 'RECEPCION',
                'name' => 'Receptor de Materiales',
                'description' => 'Recibe y verifica materiales entrantes',
                'access_level' => 4,
                'active' => true,
            ],
            [
                'role_id' => 7,
                'code' => 'LECTURA',
                'name' => 'Solo Lectura',
                'description' => 'Acceso de solo lectura al sistema',
                'access_level' => 1,
                'active' => true,
            ],
        ];

        foreach ($roles as $role) {
            OperatorRole::updateOrCreate(
                ['role_id' => $role['role_id']],
                $role
            );
        }
    }
}
