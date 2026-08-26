<?php

namespace Database\Factories;

use App\Enums\StatusInscricao;
use App\Models\InscricaoNewsletter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<InscricaoNewsletter> */
class InscricaoNewsletterFactory extends Factory
{
    protected $model = InscricaoNewsletter::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'nome' => $this->faker->name(),
            'origem' => 'blog',
            'status' => StatusInscricao::Ativo,
            'submission_id' => (string) Str::uuid(),
            'descadastro_token' => InscricaoNewsletter::novoToken(),
            'politica_versao' => '2026-08-21',
            'ip_hash' => str_repeat('c', 64),
        ];
    }
}
