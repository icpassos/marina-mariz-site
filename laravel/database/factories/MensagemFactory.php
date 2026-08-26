<?php

namespace Database\Factories;

use App\Enums\OrigemMensagem;
use App\Enums\StatusMensagem;
use App\Models\Mensagem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Mensagem> */
class MensagemFactory extends Factory
{
    protected $model = Mensagem::class;

    public function definition(): array
    {
        return [
            'submission_id' => (string) Str::uuid(),
            'origem' => OrigemMensagem::Contato,
            'nome' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'whatsapp' => '(31) 99999-0000',
            'texto' => $this->faker->sentence(),
            'status' => StatusMensagem::Nova,
            'politica_versao' => '2026-08-21',
            'ip_hash' => str_repeat('a', 64),
        ];
    }

    public function formacao(): static
    {
        return $this->state(fn (): array => [
            'origem' => OrigemMensagem::FormacaoProfissional,
            'profissao' => 'Enfermeira',
            'registro_profissional' => 'COREN 12345',
            'instituicao' => 'Hospital X',
            'modalidade' => 'Online',
        ]);
    }
}
