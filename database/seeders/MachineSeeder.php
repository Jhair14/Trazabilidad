<?php

namespace Database\Seeders;

use App\Models\Machine;
use Illuminate\Database\Seeder;

class MachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $machines = [
            [
                'machine_id' => 1,
                'code' => 'MEZCLADORA_01',
                'name' => 'Mezcladora Principal',
                'description' => 'Mezcladora de alta capacidad para materias primas',
                'image_url' => null,
                'active' => true,
            ],
            [
                'machine_id' => 2,
                'code' => 'EXTRUSORA_01',
                'name' => 'Extrusora de Plástico',
                'description' => 'Máquina extrusora para procesamiento de plástico',
                'image_url' => null,
                'active' => true,
            ],
            [
                'machine_id' => 3,
                'code' => 'HORNO_01',
                'name' => 'Horno de Secado',
                'description' => 'Horno para secado y tratamiento térmico',
                'image_url' => null,
                'active' => true,
            ],
            [
                'machine_id' => 4,
                'code' => 'ENVASADORA_01',
                'name' => 'Envasadora Automática',
                'description' => 'Máquina automática para envasado de productos',
                'image_url' => null,
                'active' => true,
            ],
            [
                'machine_id' => 5,
                'code' => 'ETIQUETADORA_01',
                'name' => 'Etiquetadora',
                'description' => 'Máquina para etiquetado de productos',
                'image_url' => null,
                'active' => true,
            ],
            [
                'machine_id' => 6,
                'code' => 'EMPAQUETADORA_01',
                'name' => 'Empaquetadora',
                'description' => 'Máquina para empaquetado final de productos',
                'image_url' => null,
                'active' => true,
            ],
            [
                'machine_id' => 7,
                'code' => 'MOLINO_01',
                'name' => 'Molino de Martillos',
                'description' => 'Molino para trituración de materiales',
                'image_url' => null,
                'active' => true,
            ],
            [
                'machine_id' => 8,
                'code' => 'TAMIZADORA_01',
                'name' => 'Tamizadora',
                'description' => 'Máquina para tamizado y clasificación',
                'image_url' => null,
                'active' => true,
            ],
        ];

        foreach ($machines as $machine) {
            Machine::updateOrCreate(
                ['machine_id' => $machine['machine_id']],
                $machine
            );
        }
    }
}
