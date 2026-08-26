<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Marco de um backup semanal de contatos bem-sucedido.
 */
class BackupDeContatos extends Model
{
    protected $table = 'backups_de_contatos';

    /** Depois disto o painel avisa que o cron pode ter parado. */
    public const DIAS_ATE_O_ALERTA = 10;

    protected $fillable = [
        'executado_em',
        'mensagens',
        'leads',
        'newsletter',
        'caminho',
    ];

    protected function casts(): array
    {
        return [
            'executado_em' => 'datetime',
            'mensagens' => 'integer',
            'leads' => 'integer',
            'newsletter' => 'integer',
        ];
    }

    public static function ultimo(): ?self
    {
        return self::query()->latest('executado_em')->first();
    }

    public function total(): int
    {
        return $this->mensagens + $this->leads + $this->newsletter;
    }

    /** Nunca rodou, ou faz tempo demais: o cron pode ter parado calado. */
    public static function atrasado(): bool
    {
        $ultimo = self::ultimo();

        return $ultimo === null
            || $ultimo->executado_em->lt(now()->subDays(self::DIAS_ATE_O_ALERTA));
    }
}
