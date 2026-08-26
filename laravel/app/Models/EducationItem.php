<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\CourseFormat;
use App\Enums\EducationType;
use App\Enums\EventFormat;
use App\Enums\MaterialFormat;
use App\Enums\TrainingModality;
use App\Http\Controllers\DownloadProtegidoController;
use App\Models\Concerns\EhConteudo;
use App\Support\Publicacao;
use BackedEnum;
use Database\Factories\EducationItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

/**
 * Um item do catalogo Educacao. O campo `type` decide quais colunas valem;
 * nenhum tipo tem pagina propria no site — nem curso, nem formacao — entao
 * aqui nao ha slug, modulo nem aula (doc 01).
 */
class EducationItem extends Model
{
    /** @use HasFactory<EducationItemFactory> */
    use EhConteudo;

    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'type', 'title', 'description', 'status', 'position', 'image_id',
        'seo_title', 'seo_description', 'seo_image_id',
        'year', 'publisher', 'external_url',
        'is_free', 'pdf_id', 'epub_id',
        'format', 'modality', 'audience', 'workload', 'seats',
        'starts_at', 'ends_at',
        'event_format', 'venue_name', 'venue_address', 'venue_city', 'venue_state',
        'material_format', 'file_id', 'requires_email', 'lead_source',
    ];

    protected function casts(): array
    {
        return [
            'type' => EducationType::class,
            'status' => ContentStatus::class,
            'format' => CourseFormat::class,
            'event_format' => EventFormat::class,
            'material_format' => MaterialFormat::class,
            'modality' => TrainingModality::class,
            'is_free' => 'boolean',
            'requires_email' => 'boolean',
            'publicado_pela_primeira_vez_em' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'year' => 'integer',
            'seats' => 'integer',
            'position' => 'integer',
        ];
    }

    public function pdf(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'pdf_id');
    }

    public function epub(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'epub_id');
    }

    public function arquivo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'file_id');
    }

    // ── o que o site consome ────────────────────────────────────────

    /**
     * O que cada pagina do site lista: so publicado, so do seu tipo, na
     * ordem do painel — evento por data (doc 01). Rascunho, arquivado e
     * lixeira ficam de fora.
     */
    public function scopeParaSite(Builder $query, EducationType $tipo): Builder
    {
        $query->publicado();

        if ($tipo === EducationType::Material) {
            // E-book gratuito aparece tambem em Materiais Gratuitos, sem
            // precisar cadastrar de novo (doc 01).
            $query->where(fn (Builder $q) => $q
                ->where('type', EducationType::Material)
                ->orWhere(fn (Builder $q) => $q
                    ->where('type', EducationType::Ebook)
                    ->where('is_free', true)));
        } else {
            $query->where('type', $tipo);
        }

        return $tipo === EducationType::Evento
            ? $query->orderBy('starts_at')
            : $query->orderBy('position');
    }

    /**
     * Como o item se apresenta em Materiais Gratuitos. O e-book gratuito
     * entra com Formato = PDF, Arquivo = o .pdf enviado e Exige e-mail =
     * sim (doc 01).
     *
     * @return array{formato: ?MaterialFormat, arquivo: ?Media, exige_email: bool}
     */
    public function comoMaterial(): array
    {
        $ebookGratuito = $this->type === EducationType::Ebook && $this->is_free;

        return [
            'formato' => $ebookGratuito ? MaterialFormat::Pdf : $this->material_format,
            'arquivo' => $ebookGratuito ? $this->pdf : $this->arquivo,
            'exige_email' => $ebookGratuito ? true : (bool) $this->requires_email,
        ];
    }

    /** Link assinado de 3 dias; nao existe URL publica para estes arquivos. */
    public function linkDeDownload(): ?string
    {
        $arquivo = $this->comoMaterial()['arquivo'];

        return $arquivo instanceof Media
            ? DownloadProtegidoController::linkPara($arquivo)
            : null;
    }

    // ── obrigatorio para publicar, nao para salvar ──────────────────

    /** Toda coluna que algum tipo pode exigir, para montar o estado do formulario. */
    public const COLUNAS_EXIGIVEIS = [
        'type', 'title', 'description', 'external_url', 'is_free', 'pdf_id',
        'format', 'modality', 'starts_at', 'ends_at', 'event_format',
        'venue_name', 'venue_address', 'venue_city', 'venue_state',
        'material_format', 'file_id',
    ];

    /**
     * Campos com * do tipo escolhido: campo => rotulo. Campo condicional
     * so entra quando esta visivel — evento online nao cobra Local e
     * e-book gratuito nao cobra link de compra (doc 01).
     *
     * @param  array<string, mixed>  $dados
     * @return array<string, string>
     */
    public static function exigidos(array $dados): array
    {
        $comuns = ['title' => 'Título', 'description' => 'Descrição'];

        $tipo = self::valor($dados['type'] ?? null);

        return match ($tipo) {
            EducationType::Livro->value => [...$comuns, 'external_url' => 'Link de compra'],

            EducationType::Ebook->value => [
                ...$comuns,
                ...($dados['is_free'] ?? false
                    ? ['pdf_id' => 'Arquivo .pdf']
                    : ['external_url' => 'Link de compra']),
            ],

            EducationType::Curso->value => [
                ...$comuns,
                'format' => 'Formato',
                'external_url' => 'Link externo',
            ],

            EducationType::Evento->value => [
                ...$comuns,
                'starts_at' => 'Data e hora de início',
                'ends_at' => 'Data e hora de fim',
                'event_format' => 'Formato',
                ...(self::valor($dados['event_format'] ?? null) === EventFormat::Presencial->value
                    ? [
                        'venue_name' => 'Nome do local',
                        'venue_address' => 'Endereço',
                        'venue_city' => 'Cidade',
                        'venue_state' => 'Estado',
                    ]
                    : []),
            ],

            EducationType::Material->value => [
                ...$comuns,
                'material_format' => 'Formato',
                'file_id' => 'Arquivo',
            ],

            EducationType::Formacao->value => [
                ...$comuns,
                'format' => 'Formato',
                'modality' => 'Modalidade',
                'external_url' => 'Link externo',
            ],

            default => $comuns,
        };
    }

    /** Enum ou string crua, o que importa e o valor. */
    private static function valor(mixed $estado): mixed
    {
        return $estado instanceof BackedEnum ? $estado->value : $estado;
    }

    protected static function booted(): void
    {
        // Ultima palavra do servidor: o Filament repete estas regras nos
        // campos, mas o estado que o Livewire manda nao e confiavel (doc 01).
        static::saving(function (self $item): void {
            $dados = $item->getAttributes();

            if (! Publicacao::vaiPublicar($dados['status'] ?? null)) {
                return;
            }

            $erros = [];

            foreach (self::exigidos($dados) as $campo => $rotulo) {
                if (blank($dados[$campo] ?? null)) {
                    $erros[$campo] = "\"{$rotulo}\" é obrigatório para publicar.";
                }
            }

            if ($erros !== []) {
                throw ValidationException::withMessages($erros);
            }
        });
    }
}
