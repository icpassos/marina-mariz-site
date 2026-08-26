<?php

namespace App\Models;

use Database\Factories\OutboxJobFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Um item da outbox: um envio de formulario a avisar por e-mail (doc 08).
 */
class OutboxJob extends Model
{
    /** @use HasFactory<OutboxJobFactory> */
    use HasFactory;

    public const PENDENTE = 'pendente';

    public const ENTREGUE = 'entregue';

    public const FALHOU = 'falhou';

    protected $fillable = [
        'submission_id',
        'assunto_type',
        'assunto_id',
        'status',
        'tentativas',
        'ultimo_erro',
        'proxima_tentativa_em',
        'entregue_em',
    ];

    protected function casts(): array
    {
        return [
            'tentativas' => 'integer',
            'proxima_tentativa_em' => 'datetime',
            'entregue_em' => 'datetime',
        ];
    }

    public function assunto(): MorphTo
    {
        return $this->morphTo();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::ENTREGUE => 'Entregue',
            self::FALHOU => 'Falhou',
            default => 'Pendente',
        };
    }
}
