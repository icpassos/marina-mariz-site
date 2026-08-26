<?php

namespace App\Support;

use App\Models\Media;
use App\Models\Post;
use Barryvdh\DomPDF\Facade\Pdf;
use Closure;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\Style\Language;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * "Baixar PDF" e "Baixar Word" do post (docs 02 e 08).
 *
 * Template próprio e simples: não tenta reproduzir o layout do site. Leva
 * título, categoria, autor, data, descrição, imagem, conteúdo e aviso
 * médico — e nada de status, SEO, IDs ou anotação de painel.
 *
 * Imagem vem só da Biblioteca de Mídia, por caminho local: o servidor não
 * busca URL externa em nenhum dos dois formatos.
 */
class ExportaPost
{
    /** Dentro do disco privado; nunca em `public/` (doc 08). */
    private const PASTA = 'exportacoes';

    private const AVISO = 'Este conteúdo é informativo e não substitui consulta médica. Marque sua consulta agora.';

    public static function pdf(Post $post): BinaryFileResponse
    {
        $pasta = self::pastaTemporaria();
        $arquivo = $pasta.'/post.pdf';

        Pdf::loadHTML(self::html($post))->save($arquivo);

        return self::entregar($arquivo, self::nome($post, 'pdf'));
    }

    public static function docx(Post $post): BinaryFileResponse
    {
        $pasta = self::pastaTemporaria();
        $arquivo = $pasta.'/post.docx';

        // O PHPWord não lê WebP nem data URI: cada imagem vira um PNG
        // temporário na pasta, embutido no .docx e apagado logo depois.
        $png = fn (?Media $media): ?string => self::comoPng($media, $pasta);

        $word = new PhpWord;
        $word->getSettings()->setThemeFontLang(new Language('pt-BR'));
        $secao = $word->addSection();

        $secao->addTitle($post->title, 1);
        $secao->addText(implode(' · ', array_filter([
            $post->nomeDaCategoria(),
            $post->author,
            $post->published_at?->format('d/m/Y'),
        ])), ['italic' => true]);

        if (filled($post->description)) {
            $secao->addText($post->description, ['bold' => true]);
        }

        if ($capa = $png($post->imagem)) {
            $secao->addImage($capa, ['width' => 450]);
        }

        Html::addHtml($secao, self::conteudo($post, $png), false, false);

        $secao->addTextBreak();
        $secao->addText(self::AVISO, ['size' => 9, 'italic' => true]);

        IOFactory::createWriter($word, 'Word2007')->save($arquivo);

        // Os PNGs já estão dentro do .docx; a pasta guarda só o resultado.
        foreach (File::glob($pasta.'/*.png') as $temporario) {
            File::delete($temporario);
        }

        return self::entregar($arquivo, self::nome($post, 'docx'));
    }

    /**
     * O HTML que vira PDF. Público porque é ele que carrega o conteúdo —
     * o Dompdf só converte. Imagem embutida em data URI: o servidor não
     * sai para a rede em nenhum momento.
     */
    public static function html(Post $post): string
    {
        return view('exportacoes.post', [
            'post' => $post,
            'imagem' => self::dataUri($post->imagem),
            'conteudo' => self::conteudo($post, self::dataUri(...)),
            'aviso' => self::AVISO,
        ])->render();
    }

    /** `slug-do-post-AAAA-MM-DD.ext` (doc 02). */
    public static function nome(Post $post, string $extensao): string
    {
        $data = $post->published_at ?? $post->created_at ?? now();

        return "{$post->slug}-{$data->format('Y-m-d')}.{$extensao}";
    }

    /**
     * Conteúdo rico em HTML sanitizado, com cada imagem resolvida pelo
     * `$comoArquivo` do formato. Imagem que não está na biblioteca vira
     * texto legível em vez de derrubar a exportação (doc 08).
     */
    private static function conteudo(Post $post, Closure $comoArquivo): string
    {
        return RichContentRenderer::make($post->content ?? [])
            ->processNodesUsing(function (object &$node) use ($comoArquivo): void {
                if ($node->type !== 'image') {
                    return;
                }

                $origem = $comoArquivo(Media::find($node->attrs->id ?? null));

                if ($origem === null) {
                    $node->type = 'paragraph';
                    $node->content = [(object) ['type' => 'text', 'text' => '[imagem indisponível]']];
                    unset($node->attrs);

                    return;
                }

                $node->attrs->src = $origem;
            })
            ->toHtml();
    }

    private static function dataUri(?Media $media): ?string
    {
        $absoluto = self::caminhoLocal($media);

        return $absoluto === null
            ? null
            : 'data:'.$media->mime_type.';base64,'.base64_encode(file_get_contents($absoluto));
    }

    private static function comoPng(?Media $media, string $pasta): ?string
    {
        $absoluto = self::caminhoLocal($media);

        if ($absoluto === null) {
            return null;
        }

        $imagem = @imagecreatefromstring(file_get_contents($absoluto));

        if ($imagem === false) {
            return null;
        }

        $destino = $pasta.'/'.$media->getKey().'.png';
        imagepng($imagem, $destino);
        imagedestroy($imagem);

        return $destino;
    }

    /** Só imagem cadastrada e existente no disco privado. */
    private static function caminhoLocal(?Media $media): ?string
    {
        if (! $media?->isImage() || ! Storage::disk('local')->exists($media->path)) {
            return null;
        }

        return Storage::disk('local')->path($media->path);
    }

    private static function pastaTemporaria(): string
    {
        $pasta = Storage::disk('local')->path(self::PASTA.'/'.Str::uuid());

        File::ensureDirectoryExists($pasta);

        return $pasta;
    }

    private static function entregar(string $arquivo, string $nome): BinaryFileResponse
    {
        return response()
            ->download($arquivo, $nome, ['X-Content-Type-Options' => 'nosniff'])
            ->deleteFileAfterSend();
    }
}
