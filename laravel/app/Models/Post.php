<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\PostCta;
use App\Models\Concerns\EhConteudo;
use App\Support\AnexosNaBiblioteca;
use Database\Factories\PostFactory;
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model implements HasRichContent
{
    use EhConteudo;

    /** @use HasFactory<PostFactory> */
    use HasFactory;

    use InteractsWithRichContent;
    use SoftDeletes;

    /** Padrão do campo Autor (doc 02). */
    public const AUTOR_PADRAO = 'Dra. Marina Mariz';

    public const MAXIMO_DE_RELACIONADOS = 4;

    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'description',
        'content',
        'author',
        'published_at',
        'closing_cta',
        'material_description',
        'material_media_id',
        'episode_url',
        'status',
        'position',
        'image_id',
        'seo_title',
        'seo_description',
        'seo_image_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'closing_cta' => PostCta::class,
            'published_at' => 'datetime',
            'publicado_pela_primeira_vez_em' => 'datetime',
            'content' => 'array',
            'position' => 'integer',
        ];
    }

    protected function setUpRichContent(): void
    {
        $this->registerRichContent('content')
            ->json()
            ->fileAttachmentProvider(new AnexosNaBiblioteca);
    }

    // ── relações ────────────────────────────────────────────────────

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /** Arquivo do CTA "baixar material gratuito". */
    public function arquivo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'material_media_id');
    }

    /** O que o painel edita: inclui rascunho, para poder escolher antes de publicar. */
    public function relacionados(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_related', 'post_id', 'related_post_id');
    }

    /**
     * O que o site mostra. Relacionado arquivado ou na lixeira some da
     * lista em vez de virar link quebrado (doc 02).
     */
    public function relacionadosNoAr(): BelongsToMany
    {
        return $this->relacionados()->noAr();
    }

    // ── recorte público ─────────────────────────────────────────────

    /**
     * `status = publicado AND published_at <= NOW()`. Post agendado entra
     * no site sozinho quando a data chega, sem depender de tarefa rodando
     * no horário certo (doc 02).
     */
    public function scopeNoAr(Builder $query): Builder
    {
        return $query->publicado()->where('published_at', '<=', now());
    }

    public function estaNoAr(): bool
    {
        return $this->estaPublicado() && $this->published_at?->isPast() === true;
    }

    /** "Agendado" é exibição derivada, não valor gravado. */
    public function estaAgendado(): bool
    {
        return $this->estaPublicado() && $this->published_at?->isFuture() === true;
    }

    public function rotuloDeEstado(): string
    {
        return $this->estaAgendado() ? 'Agendado' : $this->status->getLabel();
    }

    public function nomeDaCategoria(): string
    {
        return $this->categoria?->name ?? 'Sem categoria';
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post): void {
            $post->normalizar();
            $post->conferirPapel();
        });
    }

    /**
     * Slug em branco sai do título, e campo de CTA que não é do CTA
     * escolhido não fica guardado. Vale também contra payload forjado:
     * o Livewire manda o que quiser.
     */
    protected function normalizar(): void
    {
        if (blank($this->slug) && filled($this->title)) {
            $this->slug = Str::slug($this->title);
        }

        if ($this->closing_cta !== PostCta::Material) {
            $this->material_description = null;
            $this->material_media_id = null;
        }

        if ($this->closing_cta !== PostCta::Podcast) {
            $this->episode_url = null;
        }
    }

    /**
     * Arquivar e desarquivar são do Administrador (doc 00). A conferência
     * fica aqui porque formulário, ação avulsa e ação em lote passam todos
     * por `save()` — esconder o botão não autoriza nada.
     */
    protected function conferirPapel(): void
    {
        $usuario = auth()->user();

        if (! $usuario || ! $this->isDirty('status')) {
            return;
        }

        $mexeEmArquivado = $this->status === ContentStatus::Arquivado
            || $this->getRawOriginal('status') === ContentStatus::Arquivado->value;

        abort_if(
            $mexeEmArquivado && ! $usuario->can('arquivar', $this),
            403,
            'Somente o Administrador arquiva ou desarquiva um post.',
        );
    }
}
