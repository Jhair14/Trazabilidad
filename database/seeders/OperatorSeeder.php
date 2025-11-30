<?php

namespace Database\Seeders;

use App\Models\Operator;
use Illuminate\Database\Seeder;

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
        ];

        foreach ($operators as $operator) {
            Operator::updateOrCreate(
                ['operator_id' => $operator['operator_id']],
                $operator
            );
        }
    }
}

