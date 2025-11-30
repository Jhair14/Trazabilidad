<?php

namespace Database\Seeders;

use App\Models\StandardVariable;
use Illuminate\Database\Seeder;

class StandardVariableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variables = [
            [
                'variable_id' => 1,
                'code' => 'TEMPERATURA',
                'name' => 'Temperatura',
                'unit' => '°C',
                'description' => 'Temperatura del proceso',
                'active' => true,
            ],
            [
                'variable_id' => 2,
                'code' => 'PRESION',
                'name' => 'Presión',
                'unit' => 'PSI',
                'description' => 'Presión del proceso',
                'active' => true,
            ],
            [
                'variable_id' => 3,
                'code' => 'VELOCIDAD',
                'name' => 'Velocidad',
                'unit' => 'RPM',
                'description' => 'Velocidad de la máquina',
                'active' => true,
            ],
            [
                'variable_id' => 4,
                'code' => 'TIEMPO',
                'name' => 'Tiempo de Proceso',
                'unit' => 'min',
                'description' => 'Tiempo de duración del proceso',
                'active' => true,
            ],
            [
                'variable_id' => 5,
                'code' => 'HUMEDAD',
                'name' => 'Humedad',
                'unit' => '%',
                'description' => 'Nivel de humedad',
                'active' => true,
            ],
            [
                'variable_id' => 6,
                'code' => 'PH',
                'name' => 'pH',
                'unit' => 'pH',
                'description' => 'Nivel de acidez/alcalinidad',
                'active' => true,
            ],
            [
                'variable_id' => 7,
                'code' => 'PESO',
                'name' => 'Peso',
                'unit' => 'kg',
                'description' => 'Peso del producto',
                'active' => true,
            ],
            [
                'variable_id' => 8,
                'code' => 'VOLUMEN',
                'name' => 'Volumen',
                'unit' => 'L',
                'description' => 'Volumen del producto',
                'active' => true,
            ],
            [
                'variable_id' => 9,
                'code' => 'DENSIDAD',
                'name' => 'Densidad',
                'unit' => 'g/cm³',
                'description' => 'Densidad del material',
                'active' => true,
            ],
            [
                'variable_id' => 10,
                'code' => 'VISCOSIDAD',
                'name' => 'Viscosidad',
                'unit' => 'cP',
                'description' => 'Viscosidad del fluido',
                'active' => true,
            ],
            [
                'variable_id' => 11,
                'code' => 'CALIDAD',
                'name' => 'Calidad Visual',
                'unit' => 'Escala 1-10',
                'description' => 'Evaluación visual de calidad',
                'active' => true,
            ],
            [
                'variable_id' => 12,
                'code' => 'COLOR',
                'name' => 'Color',
                'unit' => 'Código',
                'description' => 'Código de color del producto',
                'active' => true,
            ],
        ];

        foreach ($variables as $variable) {
            StandardVariable::updateOrCreate(
                ['variable_id' => $variable['variable_id']],
                $variable
            );
        }
    }
}
