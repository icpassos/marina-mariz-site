<?php

namespace Database\Factories;

use App\Models\Mensagem;
use App\Models\OutboxJob;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<OutboxJob> */
class OutboxJobFactory extends Factory
{
    protected $model = OutboxJob::class;

    public function definition(): array
    {
        return [
            'submission_id' => (string) Str::uuid(),
            'assunto_type' => Mensagem::class,
            'assunto_id' => Mensagem::factory(),
            'status' => OutboxJob::PENDENTE,
            'tentativas' => 0,
            'proxima_tentativa_em' => now(),
        ];
    }
}
