<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\Post;
use Database\Seeders\BlogCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * O import roda uma vez, na maquina local, e o resultado e o que vai para
 * producao — nao ha como corrigir depois "no ar". Estes testes guardam o que
 * o export de 270 posts do WordPress trouxe de torto: permalink com data,
 * imagem em varios tamanhos, nome de arquivo repetido entre anos, bloco
 * reutilizavel que nao veio junto.
 */
class ImportacaoWordPressTest extends TestCase
{
    use RefreshDatabase;

    private string $pasta;

    protected function setUp(): void
    {
        parent::setUp();

        // Sem isto, o import de teste grava na biblioteca de verdade e deixa
        // arquivo orfao em `storage/app/private/midia`.
        Storage::fake('local');

        $this->seed(BlogCategorySeeder::class);

        $this->pasta = sys_get_temp_dir().'/wp-import-'.getmypid();

        @mkdir($this->pasta.'/uploads/2022/03', recursive: true);
        @mkdir($this->pasta.'/uploads/2023/03', recursive: true);
    }

    protected function tearDown(): void
    {
        exec('rm -rf '.escapeshellarg($this->pasta));

        parent::tearDown();
    }

    public function test_traz_post_publicado_com_imagem_destacada_e_categoria(): void
    {
        $this->importar();

        $post = Post::where('slug', 'parto-normal')->firstOrFail();

        $this->assertSame('Sobre o parto normal', $post->title);
        $this->assertSame(ContentStatus::Publicado, $post->status);
        $this->assertSame('Artigos', $post->nomeDaCategoria());
        $this->assertSame('2022-03-11 18:56:00', $post->published_at->toDateTimeString());
        $this->assertNotNull($post->image_id);
        $this->assertNotNull($post->publicado_pela_primeira_vez_em);
        $this->assertStringContainsString('Primeiro parágrafo', $post->description);
    }

    public function test_relato_de_parto_vai_para_a_categoria_propria(): void
    {
        $this->importar();

        $this->assertSame('Relatos de Parto', Post::where('slug', 'meu-relato')->firstOrFail()->nomeDaCategoria());
    }

    public function test_permalink_com_data_vira_endereco_do_blog(): void
    {
        $this->importar();

        $html = Post::where('slug', 'parto-normal')->firstOrFail()->renderRichContent('content');

        $this->assertStringContainsString('href="/blog/meu-relato"', $html);
        $this->assertStringNotContainsString('/2022/03/11/', $html);
    }

    public function test_pagina_do_wordpress_sem_equivalente_perde_o_link_e_mantem_o_texto(): void
    {
        $this->importar();

        $html = Post::where('slug', 'parto-normal')->firstOrFail()->renderRichContent('content');

        $this->assertStringNotContainsString('sample-page', $html);
        $this->assertStringContainsString('página antiga', $html);
    }

    public function test_imagem_do_corpo_aponta_para_a_biblioteca_e_nao_para_o_site_antigo(): void
    {
        $this->importar();

        $conteudo = Post::where('slug', 'parto-normal')->firstOrFail()->content;
        $imagem = collect($conteudo['content'])->firstWhere('type', 'image');

        $this->assertNotNull($imagem, 'a imagem do corpo sumiu');
        $this->assertNotNull($imagem['attrs']['id'] ?? null);
        $this->assertTrue(Media::whereKey($imagem['attrs']['id'])->exists());
    }

    public function test_nome_de_arquivo_repetido_em_anos_diferentes_nao_vira_a_mesma_imagem(): void
    {
        $this->importar();

        $um = Post::where('slug', 'parto-normal')->firstOrFail()->image_id;
        $outro = Post::where('slug', 'meu-relato')->firstOrFail()->image_id;

        $this->assertNotSame($um, $outro);
        $this->assertSame(2, Media::where('original_name', 'foto.jpg')->count());
    }

    public function test_post_sem_imagem_destacada_entra_como_rascunho(): void
    {
        $this->importar();

        $this->assertSame(ContentStatus::Rascunho, Post::where('slug', 'sem-imagem')->firstOrFail()->status);
    }

    public function test_rodar_de_novo_nao_duplica_post_nem_arquivo(): void
    {
        $this->importar();

        $posts = Post::count();
        $midias = Media::count();
        $arquivos = count(Storage::disk('local')->files('midia'));

        $this->importar();

        $this->assertSame($posts, Post::count());
        $this->assertSame($midias, Media::count());
        $this->assertSame($arquivos, count(Storage::disk('local')->files('midia')));
    }

    public function test_endereco_antigo_do_wordpress_redireciona_para_o_post(): void
    {
        $this->importar();

        $this->get('/2022/03/11/parto-normal/')->assertRedirect('/blog/parto-normal');
        $this->get('/2022/03/11/nao-existe/')->assertNotFound();
    }

    // ── fixture ─────────────────────────────────────────────────────

    private function importar(): void
    {
        $this->artisan('blog:importar', [
            'xml' => $this->escreverXml(),
            'uploads' => $this->pasta.'/uploads',
        ])->assertSuccessful();
    }

    private function escreverXml(): string
    {
        // Duas fotos diferentes com o mesmo nome, como o WordPress faz ao
        // subir "foto.jpg" em anos diferentes.
        $this->imagem($this->pasta.'/uploads/2022/03/foto.jpg', 40, 30);
        $this->imagem($this->pasta.'/uploads/2023/03/foto.jpg', 60, 20);

        $xml = <<<'XML'
        <?xml version="1.0" encoding="UTF-8" ?>
        <rss version="2.0"
            xmlns:content="http://purl.org/rss/1.0/modules/content/"
            xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
            xmlns:wp="http://wordpress.org/export/1.2/">
        <channel>
            <item>
                <title>foto 2022</title>
                <wp:post_id>10</wp:post_id>
                <wp:post_type>attachment</wp:post_type>
                <wp:status>inherit</wp:status>
                <wp:attachment_url>https://dramarinamariz.com.br/wp-content/uploads/2022/03/foto.jpg</wp:attachment_url>
                <wp:postmeta><wp:meta_key>_wp_attachment_image_alt</wp:meta_key><wp:meta_value>Mãe com o bebê</wp:meta_value></wp:postmeta>
            </item>
            <item>
                <title>foto 2023</title>
                <wp:post_id>11</wp:post_id>
                <wp:post_type>attachment</wp:post_type>
                <wp:status>inherit</wp:status>
                <wp:attachment_url>https://dramarinamariz.com.br/wp-content/uploads/2023/03/foto.jpg</wp:attachment_url>
            </item>
            <item>
                <title>Sobre o parto normal</title>
                <wp:post_id>1</wp:post_id>
                <wp:post_name>parto-normal</wp:post_name>
                <wp:post_type>post</wp:post_type>
                <wp:status>publish</wp:status>
                <wp:post_date_gmt>2022-03-11 18:56:00</wp:post_date_gmt>
                <category domain="category" nicename="gestacao">Gestação</category>
                <excerpt:encoded></excerpt:encoded>
                <content:encoded><![CDATA[
                    <!-- wp:paragraph --><p>Primeiro parágrafo, com <strong>peso</strong> e <a href="https://dramarinamariz.com.br/2022/03/11/meu-relato/">link antigo</a>.</p><!-- /wp:paragraph -->
                    <!-- wp:block {"ref":505} /-->
                    <p></p>
                    <!-- wp:heading {"level":3} --><h3>Um subtítulo</h3><!-- /wp:heading -->
                    <p>Uma <a href='https://dramarinamariz.com.br/sample-page/'>página antiga</a> que não existe mais.</p>
                    <figure class="wp-block-image"><img src="https://dramarinamariz.com.br/wp-content/uploads/2022/03/foto-682x1024.jpg" alt="" width="341" height="512" class="wp-image-10"/></figure>
                    <ul><li>Um item</li><li>Outro item</li></ul>
                ]]></content:encoded>
                <wp:postmeta><wp:meta_key>_thumbnail_id</wp:meta_key><wp:meta_value>10</wp:meta_value></wp:postmeta>
            </item>
            <item>
                <title>Meu relato</title>
                <wp:post_id>2</wp:post_id>
                <wp:post_name>meu-relato</wp:post_name>
                <wp:post_type>post</wp:post_type>
                <wp:status>publish</wp:status>
                <wp:post_date_gmt>2023-03-01 10:00:00</wp:post_date_gmt>
                <category domain="category" nicename="relatos-de-parto">Relatos de Parto</category>
                <category domain="category" nicename="gestacao">Gestação</category>
                <content:encoded><![CDATA[<p>O relato inteiro.</p>]]></content:encoded>
                <wp:postmeta><wp:meta_key>_thumbnail_id</wp:meta_key><wp:meta_value>11</wp:meta_value></wp:postmeta>
            </item>
            <item>
                <title>Sem imagem</title>
                <wp:post_id>3</wp:post_id>
                <wp:post_name>sem-imagem</wp:post_name>
                <wp:post_type>post</wp:post_type>
                <wp:status>publish</wp:status>
                <wp:post_date_gmt>2023-04-01 10:00:00</wp:post_date_gmt>
                <content:encoded><![CDATA[<p>Texto sem foto.</p>]]></content:encoded>
            </item>
            <item>
                <title>Rascunho antigo</title>
                <wp:post_id>4</wp:post_id>
                <wp:post_name>rascunho-antigo</wp:post_name>
                <wp:post_type>post</wp:post_type>
                <wp:status>draft</wp:status>
                <wp:post_date_gmt>2023-05-01 10:00:00</wp:post_date_gmt>
                <content:encoded><![CDATA[<p>Nunca foi publicado.</p>]]></content:encoded>
            </item>
        </channel>
        </rss>
        XML;

        file_put_contents($caminho = $this->pasta.'/export.xml', $xml);

        return $caminho;
    }

    private function imagem(string $caminho, int $largura, int $altura): void
    {
        $imagem = imagecreatetruecolor($largura, $altura);
        imagejpeg($imagem, $caminho);
        imagedestroy($imagem);
    }
}
