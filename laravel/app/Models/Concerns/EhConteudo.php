<?php

namespace App\Models\Concerns;

use App\Enums\ContentStatus;
use App\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vocabulario comum a todo conteudo do painel: estado, ordem, imagem e
 * SEO. As colunas vem das macros de Blueprint `conteudoBase` e
 * `conteudoSeo`, para os modulos nao divergirem no schema.
 */
trait EhConteudo
{
    protected static function bootEhConteudo(): void
    {
        // Marca a primeira publicacao uma vez so: o status vai e volta, o
        // marco nao. E ele que decide o destino dos arquivos na exclusao.
        static::saving(function (Model $conteudo): void {
            if ($conteudo->estaPublicado() && $conteudo->publicado_pela_primeira_vez_em === null) {
                $conteudo->publicado_pela_primeira_vez_em = now();
            }
        });

        // Rascunho que nunca foi ao ar leva junto o que enviou. Aqui o
        // registro ja saiu do banco, entao as chaves estrangeiras nao
        // seguram mais o arquivo e a contagem de uso sai certa.
        static::forceDeleted(fn (Model $conteudo) => Media::limparEnviosDe($conteudo));
    }

    public function imagem(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function seoImagem(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'seo_image_id');
    }

    /** Rascunho e arquivado nunca aparecem no site. */
    public function scopePublicado(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Publicado);
    }

    public function estaPublicado(): bool
    {
        return $this->status === ContentStatus::Publicado;
    }

    /** Titulo SEO vazio cai para o titulo do item (doc 00). */
    public function tituloSeo(): string
    {
        return filled($this->seo_title) ? $this->seo_title : (string) $this->title;
    }

    public function descricaoSeo(): string
    {
        return filled($this->seo_description) ? $this->seo_description : (string) $this->description;
    }
}
