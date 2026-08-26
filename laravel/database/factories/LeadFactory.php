<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Lead> */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'nome' => $this->faker->name(),
            'telefone' => '(31) 99999-0000',
            'eh_cliente' => false,
            'politica_versao' => '2026-08-21',
            'ip_hash' => str_repeat('b', 64),
        ];
    }
}
