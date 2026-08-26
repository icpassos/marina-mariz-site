<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Models\Concerns\EhConteudo;
use Database\Factories\LinkFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Um botao da pagina /links (doc 09).
 *
 * A pagina e um agregador no estilo "link na bio": vive fora do site, sem
 * entrada no menu, no rodape ou no sitemap. Quem chega vem do Instagram.
 */
class Link extends Model
{
    use EhConteudo;

    /** @use HasFactory<LinkFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'title',
        'url',
        'description',
        'status',
        'position',
        'image_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'position' => 'integer',
            'publicado_pela_primeira_vez_em' => 'datetime',
        ];
    }

    /**
     * O que a pagina mostra, na ordem definida por arrastar no painel.
     * Nao ha agendamento: link publicado esta no ar agora.
     */
    public function scopeNoAr(Builder $query): Builder
    {
        return $query->publicado()->orderBy('position')->orderBy('id');
    }

    /**
     * Link para fora abre em aba nova; link do proprio site continua na
     * mesma. Decidir pelo endereco evita um campo a mais no formulario.
     */
    public function ehExterno(): bool
    {
        $destino = parse_url($this->url, PHP_URL_HOST);

        return filled($destino) && $destino !== parse_url(config('app.url'), PHP_URL_HOST);
    }

    protected static function booted(): void
    {
        static::saving(function (Link $link): void {
            $usuario = auth()->user();

            // Arquivar e desarquivar sao do Administrador (doc 00). O
            // formulario, a acao avulsa e a acao em lote passam todas por
            // `save()` — esconder o botao nao autoriza nada.
            if (! $usuario || ! $link->isDirty('status')) {
                return;
            }

            $mexeEmArquivado = $link->status === ContentStatus::Arquivado
                || $link->getRawOriginal('status') === ContentStatus::Arquivado->value;

            abort_if(
                $mexeEmArquivado && ! $usuario->can('arquivar', $link),
                403,
                'Somente o Administrador arquiva ou desarquiva um link.',
            );
        });
    }
}
