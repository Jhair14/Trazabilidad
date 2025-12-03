<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            // Paneles
            'ver panel control',
            'ver panel cliente',
            
            // Pedidos
            'crear pedidos',
            'ver mis pedidos',
            'editar mis pedidos',
            'cancelar mis pedidos',
            'gestionar pedidos',
            'aprobar pedidos',
            'rechazar pedidos',
            
            // Materia Prima
            'ver materia prima',
            'solicitar materia prima',
            'recepcionar materia prima',
            'gestionar proveedores',
            
            // Lotes
            'gestionar lotes',
            
            // Procesos
            'gestionar maquinas',
            'gestionar procesos',
            'gestionar variables estandar',
            
            // Certificaciones
            'certificar lotes',
            'ver certificados',
            
            // Almacenes
            'almacenar lotes',
            
            // Administración
            'gestionar usuarios',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        
        // Rol: Cliente
        $clienteRole = Role::create(['name' => 'cliente']);
        $clienteRole->givePermissionTo([
            'ver panel cliente',
            'crear pedidos',
            'ver mis pedidos',
            'editar mis pedidos',
            'cancelar mis pedidos',
            'ver certificados',
        ]);

        // Rol: Operador
        $operadorRole = Role::create(['name' => 'operador']);
        $operadorRole->givePermissionTo([
            'ver panel control',
            'ver materia prima',
            'solicitar materia prima',
            'recepcionar materia prima',
            'gestionar proveedores',
            'gestionar lotes',
            'gestionar maquinas',
            'gestionar procesos',
            'gestionar variables estandar',
            'certificar lotes',
            'ver certificados',
            'almacenar lotes',
        ]);

        // Rol: Admin (todos los permisos)
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());
    }
}




