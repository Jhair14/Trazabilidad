<?php

namespace Database\Seeders;

use App\Models\Process;
use Illuminate\Database\Seeder;

class ProcessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $processes = [
            [
                'process_id' => 1,
                'code' => 'PREPARACION',
                'name' => 'Preparación de Materias Primas',
                'description' => 'Proceso de preparación y mezcla inicial de materias primas',
                'active' => true,
            ],
            [
                'process_id' => 2,
                'code' => 'MEZCLADO',
                'name' => 'Mezclado',
                'description' => 'Proceso de mezclado de componentes',
                'active' => true,
            ],
            [
                'process_id' => 3,
                'code' => 'EXTRUSION',
                'name' => 'Extrusión',
                'description' => 'Proceso de extrusión del material',
                'active' => true,
            ],
            [
                'process_id' => 4,
                'code' => 'MOLDEO',
                'name' => 'Moldeo',
                'description' => 'Proceso de moldeo del producto',
                'active' => true,
            ],
            [
                'process_id' => 5,
                'code' => 'SECADO',
                'name' => 'Secado',
                'description' => 'Proceso de secado del producto',
                'active' => true,
            ],
            [
                'process_id' => 6,
                'code' => 'TRATAMIENTO',
                'name' => 'Tratamiento Térmico',
                'description' => 'Proceso de tratamiento térmico',
                'active' => true,
            ],
            [
                'process_id' => 7,
                'code' => 'ENVASADO',
                'name' => 'Envasado',
                'description' => 'Proceso de envasado del producto final',
                'active' => true,
            ],
            [
                'process_id' => 8,
                'code' => 'ETIQUETADO',
                'name' => 'Etiquetado',
                'description' => 'Proceso de etiquetado de productos',
                'active' => true,
            ],
            [
                'process_id' => 9,
                'code' => 'EMPAQUETADO',
                'name' => 'Empaquetado',
                'description' => 'Proceso de empaquetado final',
                'active' => true,
            ],
            [
                'process_id' => 10,
                'code' => 'CONTROL_CALIDAD',
                'name' => 'Control de Calidad',
                'description' => 'Proceso de inspección y control de calidad',
                'active' => true,
            ],
        ];

        foreach ($processes as $process) {
            Process::updateOrCreate(
                ['process_id' => $process['process_id']],
                $process
            );
        }
    }
}
