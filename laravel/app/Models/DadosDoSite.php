<?php

namespace App\Models;

use App\Services\Site;
use Illuminate\Database\Eloquent\Model;

/**
 * Linha unica com contato, identificacao profissional, endereco e redes
 * (doc 03). Ninguem le este model direto: o site le pelo `Site`, que
 * guarda em cache.
 */
class DadosDoSite extends Model
{
    /** Conjunto fixo de redes do doc 03, na ordem em que aparecem. */
    public const REDES = [
        'instagram' => 'Instagram',
        'youtube' => 'YouTube',
        'linkedin' => 'LinkedIn',
        'spotify' => 'Spotify',
        'whatsapp' => 'WhatsApp',
        'comunidade' => 'Comunidade Sem Neura',
    ];

    protected $table = 'dados_do_site';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'especialidades' => 'array',
            'redes' => 'array',
        ];
    }

    public static function instancia(): self
    {
        return static::firstOrCreate([]);
    }

    protected static function booted(): void
    {
        // Invalidar aqui, e nao na tela, garante que qualquer caminho que
        // grave (painel, tinker, seeder) derrube o cache.
        static::saved(fn () => Site::limparCache());
    }
}
