<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Consentimento opcional e especifico (doc 04 e Politica, item 3).
 *
 * Ciencia da Politica NAO entra aqui: ela fica no proprio registro, porque
 * nao e consentimento e nao se revoga.
 */
class Consentimento extends Model
{
    protected $table = 'consentimentos';

    public const NEWSLETTER = 'newsletter';

    protected $fillable = [
        'consentivel_type',
        'consentivel_id',
        'finalidade',
        'versao_aviso',
        'ip_hash',
        'concedido_em',
        'revogado_em',
    ];

    protected function casts(): array
    {
        return [
            'concedido_em' => 'datetime',
            'revogado_em' => 'datetime',
        ];
    }

    public function consentivel(): MorphTo
    {
        return $this->morphTo();
    }
}
