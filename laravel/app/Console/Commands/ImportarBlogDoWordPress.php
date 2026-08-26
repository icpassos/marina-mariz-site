<?php

namespace App\Console\Commands;

use App\Enums\ContentStatus;
use App\Enums\PostCta;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleXMLElement;

/**
 * Traz o blog do WordPress antigo para o banco: posts, imagens e categorias.
 *
 * Roda uma vez, na maquina local, antes do deploy. O resultado (linhas no
 * banco + arquivos em `storage/app/private/midia`) e o que o `build.sh`
 * empacota — nao ha importacao rodando em producao.
 *
 * Repetir o comando nao duplica nada: post casa pelo slug e imagem casa pelo
 * nome original do arquivo.
 */
class ImportarBlogDoWordPress extends Command
{
    protected $signature = 'blog:importar
        {xml : Arquivo .xml exportado pelo WordPress (WXR)}
        {uploads : Pasta wp-content/uploads do site antigo}
        {--rascunho : Importa tudo como rascunho, sem publicar}';

    protected $description = 'Importa posts e imagens de um export do WordPress';

    /**
     * Categoria do WordPress => categoria daqui (doc 02). O blog nasce com
     * quatro categorias; as onze do site antigo sao assuntos de artigo, com
     * a unica excecao dos relatos de parto.
     */
    private const CATEGORIAS = ['relatos-de-parto' => 'relatos-de-parto'];

    private const CATEGORIA_PADRAO = 'artigos';

    /** wp:post_id do anexo => dados lidos do XML. */
    private array $anexos = [];

    /** caminho normalizado do arquivo => wp:post_id do anexo. */
    private array $anexosPorArquivo = [];

    /** caminho do arquivo => id na biblioteca (null = arquivo nao veio no backup). */
    private array $importados = [];

    private string $uploads;

    private array $semImagem = [];

    private array $imagensPerdidas = [];

    public function handle(): int
    {
        $xml = (string) $this->argument('xml');
        $this->uploads = rtrim((string) $this->argument('uploads'), '/');

        if (! is_file($xml) || ! is_dir($this->uploads)) {
            $this->error('XML ou pasta de uploads não encontrado.');

            return self::FAILURE;
        }

        $raiz = simplexml_load_file($xml, options: LIBXML_NOCDATA | LIBXML_NOENT | LIBXML_NONET);

        if ($raiz === false) {
            $this->error('XML inválido.');

            return self::FAILURE;
        }

        $itens = $raiz->channel->item;

        $this->mapearAnexos($itens);

        $posts = [];

        foreach ($itens as $item) {
            if ($this->wp($item, 'post_type') === 'post' && $this->wp($item, 'status') === 'publish') {
                $posts[] = $item;
            }
        }

        $barra = $this->output->createProgressBar(count($posts));
        $barra->start();

        foreach ($posts as $item) {
            $this->importarPost($item);
            $barra->advance();
        }

        $barra->finish();
        $this->newLine(2);

        $this->relatar();

        return self::SUCCESS;
    }

    // ── leitura do XML ──────────────────────────────────────────────

    private function mapearAnexos(SimpleXMLElement $itens): void
    {
        foreach ($itens as $item) {
            if ($this->wp($item, 'post_type') !== 'attachment') {
                continue;
            }

            $url = (string) $item->children('wp', true)->attachment_url;
            $arquivo = $this->caminhoRelativo($url);

            if (blank($arquivo)) {
                continue;
            }

            $id = $this->wp($item, 'post_id');

            $this->anexos[$id] = [
                'arquivo' => $arquivo,
                'nome' => (string) $item->title,
                'alt' => $this->meta($item, '_wp_attachment_image_alt'),
            ];

            // A mesma imagem e citada no corpo em varios tamanhos
            // (`foto-682x1024.jpg`); a chave sem o sufixo casa todos.
            $this->anexosPorArquivo[$this->semSufixoDeTamanho($arquivo)] = $id;
        }
    }

    private function importarPost(SimpleXMLElement $item): void
    {
        $titulo = trim((string) $item->title);
        $slug = (string) $this->wp($item, 'post_name');
        $slug = filled($slug) ? $slug : Str::slug($titulo);

        $html = $this->prepararHtml((string) $item->children('content', true)->encoded);
        $conteudo = $this->paraTipTap($html);

        $imagem = $this->midiaDe($this->meta($item, '_thumbnail_id'));

        if ($imagem === null) {
            $this->semImagem[] = $slug;
        }

        $descricao = $this->descricao($item, $conteudo);

        // So publica o que tem tudo que a tela de publicar exige. O que
        // falta algo entra como rascunho, aparece no painel e espera revisao.
        $completo = filled($titulo) && filled($descricao) && filled($conteudo) && $imagem !== null;

        $post = Post::withTrashed()->firstOrNew(['slug' => $slug]);

        $post->fill([
            'title' => $titulo,
            'category_id' => $this->categoriaDe($item),
            'description' => $descricao,
            'content' => $conteudo,
            'author' => Post::AUTOR_PADRAO,
            'published_at' => $this->wp($item, 'post_date_gmt'),
            'closing_cta' => PostCta::Newsletter,
            'status' => ($completo && ! $this->option('rascunho'))
                ? ContentStatus::Publicado
                : ContentStatus::Rascunho,
            'image_id' => $imagem,
        ])->save();
    }

    private function categoriaDe(SimpleXMLElement $item): ?int
    {
        $slugs = [];

        foreach ($item->category as $categoria) {
            if ((string) $categoria['domain'] === 'category') {
                $slugs[] = (string) $categoria['nicename'];
            }
        }

        $alvo = self::CATEGORIA_PADRAO;

        foreach ($slugs as $slug) {
            if (isset(self::CATEGORIAS[$slug])) {
                $alvo = self::CATEGORIAS[$slug];

                break;
            }
        }

        return Category::where('slug', $alvo)->value('id');
    }

    /**
     * Resumo do post. O WordPress so preencheu o campo em 11 dos 270; nos
     * outros, o comeco do texto e o que o card do blog mostraria de qualquer
     * jeito. O campo aceita 300 caracteres.
     */
    private function descricao(SimpleXMLElement $item, ?array $conteudo): ?string
    {
        $resumo = trim(strip_tags((string) $item->children('excerpt', true)->encoded));

        if (blank($resumo)) {
            $resumo = trim(RichContentRenderer::make($conteudo ?? [])->toText());
        }

        $resumo = preg_replace('/\s+/u', ' ', $resumo);

        return blank($resumo) ? null : Str::limit($resumo, 297);
    }

    // ── conteúdo ────────────────────────────────────────────────────

    /**
     * Deixa o HTML do WordPress no formato que o editor rico entende.
     *
     * O que sai: comentario de bloco do Gutenberg, marcacao que o editor nao
     * tem (svg, script, iframe) e imagem cujo arquivo nao veio no export. O
     * `<img>` que veio ganha `data-id`, que e como o editor guarda o vinculo
     * com a Biblioteca de Midia.
     */
    private function prepararHtml(string $html): string
    {
        // Blocos reutilizaveis (`<!-- wp:block {"ref":505} /-->`) nao vem no
        // export: o WordPress guarda o conteudo deles em outro post. Somem
        // junto com os comentarios, e nao ha o que recuperar.
        $html = preg_replace('/<!--.*?-->/s', '', $html);

        $html = preg_replace('#<(script|style|svg|iframe|form)\b[^>]*>.*?</\1>#is', '', $html);

        // Galeria e lightbox do tema antigo escrevem `href='...'`; o resto do
        // export usa aspas duplas. Uniformizar aqui evita repetir as duas
        // formas em cada expressao abaixo.
        $html = preg_replace('#\b(href|src)=\'([^\']*)\'#i', '$1="$2"', $html);

        // Legenda vira paragrafo em italico logo abaixo da imagem.
        $html = preg_replace('#<figcaption[^>]*>(.*?)</figcaption>#is', '<p><em>$1</em></p>', $html);

        $html = preg_replace_callback('#<img\b[^>]*>#i', $this->reescreverImagem(...), $html);

        $html = $this->reescreverLinks($html);

        // O export passou pelo `wpautop`: com os comentarios fora sobraram
        // paragrafos vazios, que virariam linha em branco no post.
        $html = preg_replace('#<p>(?:\s|&nbsp;)*</p>#iu', '', $html);

        return trim($html);
    }

    private function reescreverImagem(array $achado): string
    {
        $tag = $achado[0];

        preg_match('/\bsrc="([^"]+)"/i', $tag, $src);

        $arquivo = $this->semSufixoDeTamanho($this->caminhoRelativo($src[1] ?? ''));
        $anexo = $this->anexosPorArquivo[$arquivo] ?? null;

        // Nem toda imagem do corpo esta cadastrada como anexo no export; se o
        // arquivo veio na pasta de uploads, ele entra na biblioteca do mesmo
        // jeito em vez de a imagem sumir do post.
        $midia = $anexo !== null
            ? $this->midiaDe($anexo)
            : $this->registrar($arquivo, nome: null, alt: null);

        if ($midia === null) {
            // Sem arquivo na biblioteca a imagem viraria link quebrado: o
            // endereco antigo aponta para `wp-content`, que deixa de existir.
            $this->imagensPerdidas[] = $src[1] ?? '(sem src)';

            return '';
        }

        // Largura e altura do WordPress sao do tamanho recortado, que nao
        // existe mais; deixar fora faz o post usar a largura do texto.
        $tag = preg_replace('/\s(width|height|class|srcset|sizes|loading)="[^"]*"/i', '', $tag);

        return str_replace('<img', '<img data-id="'.$midia.'"', $tag);
    }

    /**
     * Endereco do site antigo que o texto cita por dentro.
     *
     * O WordPress usava permalink com data (`/2022/03/11/slug/`); aqui o post
     * mora em `/blog/slug`. Sem esta troca, 405 links dentro dos proprios
     * posts cairiam em 404 no dia do deploy.
     *
     * O que nao tem equivalente no site novo (paginas do WordPress que nao
     * foram refeitas) perde o link e mantem o texto.
     */
    private function reescreverLinks(string $html): string
    {
        $html = preg_replace(
            '#href="https?://(?:www\.)?dramarinamariz\.com\.br/\d{4}/\d{2}/\d{2}/([^/"?\#]+)/?[^"]*"#i',
            'href="/blog/$1"',
            $html,
        );

        // Landing pages dos e-books viraram o catalogo de Educacao.
        $html = preg_replace(
            '#href="https?://(?:www\.)?dramarinamariz\.com\.br/(?:plano-de-parto|estou-gravida-e-agora-dra-marina)/?[^"]*"#i',
            'href="/educacao/ebooks"',
            $html,
        );

        // Busca do WordPress (`/?s=termo`) => busca do blog (`/blog?q=termo`).
        $html = preg_replace(
            '#href="https?://(?:www\.)?dramarinamariz\.com\.br/\?s=([^"]*)"#i',
            'href="/blog?q=$1"',
            $html,
        );

        $html = preg_replace(
            '#href="https?://(?:www\.)?dramarinamariz\.com\.br/?(blog)?/?"#i',
            'href="/$1"',
            $html,
        );

        // Pagina do WordPress que nao foi refeita, e imagem aberta em tamanho
        // cheio: os dois enderecos morrem no deploy. O texto (ou a imagem)
        // fica, o link sai.
        return preg_replace(
            '#<a\b[^>]*href="https?://(?:www\.)?dramarinamariz\.com\.br/(?:sample-page|glossario|wp-content)[^"]*"[^>]*>(.*?)</a>#is',
            '$1',
            $html,
        );
    }

    /**
     * HTML => JSON do TipTap, com as mesmas extensoes que o editor do painel
     * usa. E a conversao do proprio Filament: o que ele salva ao colar um
     * texto e exatamente isto.
     */
    private function paraTipTap(string $html): ?array
    {
        if (blank($html)) {
            return null;
        }

        $documento = RichContentRenderer::make($html)->toArray();

        $documento['content'] = array_values(array_filter(
            array_map($this->emBloco(...), $documento['content'] ?? []),
            fn (array $no): bool => $no['type'] !== 'paragraph' || filled($no['content'] ?? []),
        ));

        return filled($documento['content']) ? $documento : null;
    }

    /**
     * Texto solto na raiz do documento vira paragrafo. Acontece com o bloco
     * de botao do WordPress, que e um `<a>` dentro de `<div>`: sem bloco em
     * volta, o editor nao sabe onde encaixar a linha.
     *
     * @param  array<string, mixed>  $no
     * @return array<string, mixed>
     */
    private function emBloco(array $no): array
    {
        return in_array($no['type'], ['text', 'hardBreak'], strict: true)
            ? ['type' => 'paragraph', 'content' => [$no]]
            : $no;
    }

    // ── mídia ───────────────────────────────────────────────────────

    /** Copia o arquivo para o disco privado e registra na biblioteca. */
    private function midiaDe(?string $anexo): ?int
    {
        if (blank($anexo) || ! isset($this->anexos[$anexo])) {
            return null;
        }

        $dados = $this->anexos[$anexo];

        return $this->registrar($dados['arquivo'], $dados['nome'], $dados['alt']);
    }

    /**
     * Um arquivo da pasta de uploads vira uma linha da biblioteca.
     *
     * Casa pelo nome original: rodar o comando de novo, ou a mesma imagem
     * aparecer em dois posts, reaproveita o registro em vez de encher a
     * biblioteca com copias.
     */
    private function registrar(string $relativo, ?string $nome, ?string $alt): ?int
    {
        if (blank($relativo)) {
            return null;
        }

        if (array_key_exists($relativo, $this->importados)) {
            return $this->importados[$relativo];
        }

        $origem = $this->uploads.'/'.$relativo;

        if (! is_file($origem)) {
            return $this->importados[$relativo] = null;
        }

        $nomeOriginal = basename($relativo);

        // Nome mais tamanho, nao so o nome: o WordPress reaproveita
        // `Blog-Dra-Marina-Mariz.jpg` ano apos ano com fotos diferentes, e
        // casar so pelo nome daria a mesma imagem a dezenas de posts.
        $existente = Media::where('original_name', $nomeOriginal)
            ->where('size', filesize($origem))
            ->value('id');

        if ($existente !== null) {
            return $this->importados[$relativo] = $existente;
        }

        $caminho = 'midia/'.Str::uuid().'.'.pathinfo($nomeOriginal, PATHINFO_EXTENSION);

        Storage::disk('local')->put($caminho, file_get_contents($origem));

        $midia = Media::create([
            'uuid' => (string) Str::uuid(),
            'path' => $caminho,
            'original_name' => $nomeOriginal,
            'name' => filled($nome) ? $nome : pathinfo($nomeOriginal, PATHINFO_FILENAME),
            'alt' => $alt,
            'is_decorative' => blank($alt),
            ...Media::metadadosDe($caminho),
        ]);

        return $this->importados[$relativo] = $midia->getKey();
    }

    // ── utilidades ──────────────────────────────────────────────────

    private function wp(SimpleXMLElement $item, string $campo): string
    {
        return (string) $item->children('wp', true)->{$campo};
    }

    private function meta(SimpleXMLElement $item, string $chave): ?string
    {
        foreach ($item->children('wp', true)->postmeta as $meta) {
            if ((string) $meta->meta_key === $chave) {
                $valor = trim((string) $meta->meta_value);

                return blank($valor) ? null : $valor;
            }
        }

        return null;
    }

    /** `https://site/wp-content/uploads/2022/03/foto.jpg` => `2022/03/foto.jpg`. */
    private function caminhoRelativo(string $url): string
    {
        if (! str_contains($url, '/wp-content/uploads/')) {
            return '';
        }

        return ltrim(explode('/wp-content/uploads/', $url, 2)[1], '/');
    }

    private function semSufixoDeTamanho(string $arquivo): string
    {
        return preg_replace('/-(?:\d+x\d+|scaled)(\.[a-z0-9]+)$/i', '$1', $arquivo);
    }

    private function relatar(): void
    {
        $this->info('Posts no banco: '.Post::count().'  (publicados: '.Post::publicado()->count().')');
        $this->info('Arquivos na biblioteca: '.count(array_filter($this->importados)));

        if ($this->semImagem !== []) {
            $this->warn('Sem imagem destacada, ficaram em rascunho: '.implode(', ', $this->semImagem));
        }

        if ($this->imagensPerdidas !== []) {
            $this->warn('Imagens do corpo sem arquivo no export ('.count($this->imagensPerdidas).'), removidas:');
            $this->line('  '.implode(PHP_EOL.'  ', array_unique($this->imagensPerdidas)));
        }
    }
}
