<?php

namespace App\Models;

use App\Rules\ArquivoDeMidia;
use App\Support\AnexosNaBiblioteca;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    protected $table = 'media';

    /** Mesma qualidade que o site usa nas imagens do tema. */
    public const QUALIDADE_WEBP = 82;

    protected $fillable = [
        'uuid',
        'path',
        'original_name',
        'name',
        'mime_type',
        'size',
        'width',
        'height',
        'alt',
        'is_decorative',
        'uploaded_by',
    ];

    /**
     * Onde um arquivo pode estar sendo usado: classe => [modulo, coluna => rotulo].
     * E a mesma lista que as chaves estrangeiras com `restrictOnDelete` protegem;
     * as duas precisam andar juntas, senao a tela diz "sem uso" e o banco recusa.
     */
    private const CONSUMIDORES = [
        Post::class => ['Blog', [
            'image_id' => 'imagem',
            'seo_image_id' => 'imagem de SEO',
            'material_media_id' => 'arquivo do CTA',
        ]],
        EducationItem::class => ['Educação', [
            'image_id' => 'imagem',
            'seo_image_id' => 'imagem de SEO',
            'pdf_id' => 'PDF do e-book',
            'epub_id' => 'EPUB do e-book',
            'file_id' => 'arquivo do material',
        ]],
        Link::class => ['Links', [
            'image_id' => 'miniatura',
        ]],
        Configuracao::class => ['Configurações', [
            'seo_image_id' => 'imagem de compartilhamento padrão',
        ]],
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'is_decorative' => 'boolean',
        ];
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * URL assinada e temporaria do disco privado. Nao existe caminho
     * publico para estes arquivos.
     */
    public function url(): string
    {
        return Storage::disk('local')->temporaryUrl($this->path, now()->addMinutes(30));
    }

    // ── entrada e troca de arquivo ──────────────────────────────────

    /**
     * Registra na biblioteca um arquivo recem-enviado por qualquer tela do
     * painel (doc 05): o envio acontece onde a pessoa ja esta, e o item
     * guarda so a referencia.
     */
    public static function daUpload(UploadedFile $arquivo): self
    {
        $caminho = self::guardar($arquivo);

        return self::create([
            'uuid' => (string) Str::uuid(),
            'path' => $caminho,
            'original_name' => $arquivo->getClientOriginalName(),
            'name' => pathinfo($arquivo->getClientOriginalName(), PATHINFO_FILENAME),
            'uploaded_by' => auth()->id(),
            ...self::metadadosDe($caminho),
        ]);
    }

    /**
     * Troca o arquivo servido mantendo o mesmo registro: id, nome e alt
     * ficam, mime/peso/dimensoes sao relidos do arquivo novo. Como todo
     * item aponta para o id, a troca vale em todos de uma vez (doc 05).
     */
    public function substituirArquivo(UploadedFile $arquivo): void
    {
        $antigo = $this->path;

        $caminho = self::guardar($arquivo);

        $this->fill([
            'path' => $caminho,
            'original_name' => $arquivo->getClientOriginalName(),
            ...self::metadadosDe($caminho),
        ])->save();

        // O caminho anterior deixa de existir: link assinado antigo morre junto.
        Storage::disk('local')->delete($antigo);
    }

    /**
     * Nome fisico sempre em UUID, para nao virar caminho adivinhavel.
     *
     * Imagem entra convertida em WebP e no maximo com 1920px de largura
     * (doc 05). A conversao e aqui, no servidor: fiar-se no navegador
     * recusava foto de celular e dependia de JS que podia nem carregar.
     */
    private static function guardar(UploadedFile $arquivo): string
    {
        $nome = (string) Str::uuid();

        if (str_starts_with((string) $arquivo->getMimeType(), 'image/')) {
            return self::guardarComoWebp($arquivo, 'midia/'.$nome.'.webp');
        }

        return $arquivo->storeAs(
            'midia',
            $nome.'.'.$arquivo->getClientOriginalExtension(),
            ['disk' => 'local'],
        );
    }

    /** Converte para WebP, reduz o que passa de 1920px e grava no disco. */
    private static function guardarComoWebp(UploadedFile $arquivo, string $caminho): string
    {
        $imagem = function_exists('imagewebp')
            ? @imagecreatefromstring((string) file_get_contents((string) $arquivo->getRealPath()))
            : false;

        if ($imagem === false) {
            // Servidor sem WebP no GD, ou formato que o GD nao abre: guardar o
            // original serve melhor a quem enviou do que perder o arquivo.
            return $arquivo->storeAs(
                'midia',
                pathinfo($caminho, PATHINFO_FILENAME).'.'.$arquivo->getClientOriginalExtension(),
                ['disk' => 'local'],
            );
        }

        // GIF e PNG de paleta: o WebP so aceita cor real.
        imagepalettetotruecolor($imagem);

        if (imagesx($imagem) > ArquivoDeMidia::LARGURA_MAXIMA) {
            $reduzida = imagescale($imagem, ArquivoDeMidia::LARGURA_MAXIMA);

            if ($reduzida !== false) {
                imagedestroy($imagem);
                $imagem = $reduzida;
            }
        }

        // Sem isto o PNG com fundo transparente vira um retangulo preto.
        imagealphablending($imagem, false);
        imagesavealpha($imagem, true);

        $temporario = (string) tempnam(sys_get_temp_dir(), 'webp');
        imagewebp($imagem, $temporario, self::QUALIDADE_WEBP);
        imagedestroy($imagem);

        Storage::disk('local')->put($caminho, (string) file_get_contents($temporario));
        unlink($temporario);

        return $caminho;
    }

    /**
     * Le mime, peso e dimensoes do arquivo ja gravado no disco privado.
     * Nada aqui vem do navegador.
     *
     * @return array{mime_type: string, size: int, width: ?int, height: ?int}
     */
    public static function metadadosDe(string $path): array
    {
        $disco = Storage::disk('local');
        $absoluto = $disco->path($path);

        $dados = [
            'mime_type' => (new \finfo(FILEINFO_MIME_TYPE))->file($absoluto) ?: 'application/octet-stream',
            'size' => $disco->size($path),
            'width' => null,
            'height' => null,
        ];

        if (str_starts_with($dados['mime_type'], 'image/')) {
            $dimensoes = @getimagesize($absoluto);

            if ($dimensoes !== false) {
                [$dados['width'], $dados['height']] = $dimensoes;
            }
        }

        return $dados;
    }

    // ── uso ─────────────────────────────────────────────────────────

    /**
     * Onde este arquivo aparece, em texto pronto para a tela. Item na
     * lixeira entra na conta: a chave estrangeira continua apontando para
     * ca e e ela que bloqueia a exclusao.
     *
     * @return array<int, string>
     */
    public function usos(): array
    {
        $usos = [];

        foreach (self::CONSUMIDORES as $classe => [$modulo, $campos]) {
            $usaLixeira = in_array(SoftDeletes::class, class_uses_recursive($classe), strict: true);

            foreach ($campos as $coluna => $rotulo) {
                $registros = ($usaLixeira ? $classe::withTrashed() : $classe::query())
                    ->where($coluna, $this->getKey())
                    ->get();

                foreach ($registros as $registro) {
                    $usos[] = $modulo.' · '.($registro->title ?? 'geral')." ({$rotulo})";
                }
            }
        }

        return [...$usos, ...$this->usosNoCorpoDePost()];
    }

    /**
     * Imagem colada no editor rico vive como id dentro do JSON de `content`,
     * sem chave estrangeira: sem varrer o conteudo a tela diria "sem uso" e
     * deixaria apagar uma imagem que esta no meio de um post.
     *
     * ponytail: varre os posts com corpo preenchido; se o blog crescer muito,
     * a saida e uma tabela de vinculo gravada no save.
     *
     * @return array<int, string>
     */
    private function usosNoCorpoDePost(): array
    {
        $usos = [];

        $posts = Post::withTrashed()->whereNotNull('content')->get(['id', 'title', 'content']);

        foreach ($posts as $post) {
            if (in_array($this->getKey(), AnexosNaBiblioteca::idsEm($post->content), strict: true)) {
                $usos[] = "Blog · {$post->title} (imagem no corpo)";
            }
        }

        return $usos;
    }

    /**
     * Faxina de quando o conteudo ja saiu do banco: o que nunca foi publicado
     * leva junto os arquivos que enviou; o que ja esteve no ar deixa tudo na
     * biblioteca. Roda no `forceDeleted`, nunca na lixeira — item na lixeira
     * ainda volta, e voltar sem os arquivos seria pior do que deixar sobra.
     */
    public static function limparEnviosDe(Model $conteudo): void
    {
        if ($conteudo->publicado_pela_primeira_vez_em !== null) {
            return;
        }

        $colunas = array_keys(self::CONSUMIDORES[$conteudo::class][1] ?? []);

        $enviados = [
            ...array_map(fn (string $coluna): mixed => $conteudo->getAttribute($coluna), $colunas),
            ...AnexosNaBiblioteca::idsEm($conteudo->getAttribute('content')),
        ];

        foreach (self::whereKey(array_filter($enviados))->get() as $media) {
            // Arquivo que outro conteudo tambem usa nunca e apagado.
            if ($media->usos() === []) {
                $media->delete();
            }
        }
    }

    protected static function booted(): void
    {
        static::deleted(function (Media $media): void {
            Storage::disk('local')->delete($media->path);
        });
    }
}
