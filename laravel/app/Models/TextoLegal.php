<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Services\Site;
use Illuminate\Database\Eloquent\Model;

/**
 * Politica de Privacidade e Termos de Uso (doc 03). Guarda duas copias:
 * a de trabalho, que o administrador edita, e a que esta no ar.
 */
class TextoLegal extends Model
{
    public const CHAVES = [
        'politica-de-privacidade' => 'Política de Privacidade',
        'termos-de-uso' => 'Termos de Uso',
    ];

    protected $table = 'textos_legais';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'publicado_em' => 'datetime',
        ];
    }

    /**
     * Salvar rascunho nao muda a pagina publica; publicar troca o texto
     * em uso. Nao ha historico: a copia anterior e sobrescrita.
     *
     * @param  array<string, mixed>  $dados
     */
    public function guardar(array $dados): void
    {
        $this->fill($dados);

        if ($this->status === ContentStatus::Publicado) {
            $this->titulo_publicado = $this->titulo;
            $this->conteudo_publicado = $this->conteudo;
            $this->publicado_em = now();
        }

        $this->save();
    }

    protected static function booted(): void
    {
        static::saved(fn () => Site::limparCache());
    }
}
