<?php

namespace Tests\Feature;

use App\Enums\PostCta;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Paginas publicas do blog: /blog, /blog?q=… e /blog/{slug}.
 *
 * O que estes testes defendem: o recorte publico
 * (`status = publicado AND published_at <= NOW()`) vale em lista, busca,
 * post e relacionados; as abas sao o painel e nao uma lista em codigo; a
 * paginacao tem endereco proprio; e a busca nao deixa rastro.
 */
class BlogSiteTest extends TestCase
{
    use RefreshDatabase;

    // ── lista ────────────────────────────────────────────────────────

    public function test_a_lista_traz_so_o_que_esta_no_ar_doze_por_pagina(): void
    {
        // Publicados com datas distintas, do mais novo para o mais antigo.
        foreach (range(1, 15) as $n) {
            Post::factory()->publicado()->create([
                'title' => "No ar {$n}",
                'slug' => "no-ar-{$n}",
                'published_at' => now()->subDays(30 - $n),
            ]);
        }

        Post::factory()->create(['title' => 'Rascunho fora do site']);
        Post::factory()->arquivado()->create(['title' => 'Arquivado fora do site']);
        Post::factory()->agendado()->create(['title' => 'Agendado fora do site']);
        Post::factory()->publicado()->create(['title' => 'Lixeira fora do site'])->delete();

        $primeira = $this->get('/blog');
        $primeira->assertOk();
        // 12 por pagina: os tres mais antigos ficam para a segunda.
        $this->assertSame(12, substr_count($primeira->getContent(), 'class="post-card"'));
        $primeira->assertSee('No ar 15', false);
        $primeira->assertSee('No ar 4', false);
        $primeira->assertDontSee('>No ar 3<', false);

        foreach (['Rascunho fora do site', 'Arquivado fora do site', 'Agendado fora do site', 'Lixeira fora do site'] as $fora) {
            $primeira->assertDontSee($fora, false);
        }

        $segunda = $this->get('/blog?pagina=2');
        $segunda->assertOk();
        $this->assertSame(3, substr_count($segunda->getContent(), 'class="post-card"'));
        $segunda->assertSee('No ar 3', false);
        $segunda->assertSee('No ar 1', false);
        $segunda->assertDontSee('>No ar 15<', false);
    }

    public function test_a_paginacao_tem_endereco_proprio_e_carrega_o_filtro(): void
    {
        $categoria = Category::factory()->create(['slug' => 'artigos', 'name' => 'Artigos']);
        Post::factory()->publicado()->count(15)->create(['category_id' => $categoria->getKey()]);

        $resposta = $this->get('/blog?categoria=artigos');

        $resposta->assertOk();
        // Numero de pagina, nao rolagem infinita: o link existe no HTML.
        $resposta->assertSee('categoria=artigos', false);
        $resposta->assertSee('pagina=2', false);
        $resposta->assertSee('<ol class="pager">', false);
        // Canonical proprio da pagina 2.
        $this->get('/blog?categoria=artigos&pagina=2')
            ->assertSee('<link rel="canonical" href="https://dramarinamariz.com.br/blog?categoria=artigos&amp;pagina=2">', false);
    }

    public function test_uma_pagina_so_nao_renderiza_paginacao(): void
    {
        Post::factory()->publicado()->count(3)->create();

        $this->get('/blog')->assertDontSee('<ol class="pager">', false);
    }

    // ── recorte publico ──────────────────────────────────────────────

    public function test_post_agendado_nao_aparece_nem_abre_ate_a_data_chegar(): void
    {
        $post = Post::factory()->agendado()->create([
            'title' => 'Sai na semana que vem',
            'slug' => 'sai-na-semana-que-vem',
            'published_at' => now()->addWeek(),
        ]);

        $this->get('/blog')->assertDontSee('Sai na semana que vem', false);
        $this->get('/blog/sai-na-semana-que-vem')->assertNotFound();

        $this->travelTo($post->published_at->copy()->addMinute());

        $this->get('/blog')->assertSee('Sai na semana que vem', false);
        $this->get('/blog/sai-na-semana-que-vem')->assertOk();
    }

    /** Nada de redirect e nada de 410: o 404 padrao do site responde. */
    public function test_rascunho_arquivado_e_lixeira_respondem_404(): void
    {
        Post::factory()->create(['slug' => 'rascunho']);
        Post::factory()->arquivado()->create(['slug' => 'arquivado']);
        Post::factory()->publicado()->create(['slug' => 'lixeira'])->delete();

        foreach (['rascunho', 'arquivado', 'lixeira', 'nunca-existiu'] as $slug) {
            $resposta = $this->get("/blog/{$slug}");
            $resposta->assertNotFound();
            $resposta->assertSee('Essa página não existe mais.', false);
        }
    }

    // ── abas ─────────────────────────────────────────────────────────

    public function test_as_abas_sao_as_categorias_com_post_publicado_na_ordem_do_painel(): void
    {
        $episodios = Category::factory()->create(['name' => 'Episódios', 'slug' => 'episodios', 'position' => 1]);
        $artigos = Category::factory()->create(['name' => 'Artigos', 'slug' => 'artigos', 'position' => 0]);
        Category::factory()->create(['name' => 'Relatos de Parto', 'slug' => 'relatos-de-parto', 'position' => 2]);

        Post::factory()->publicado()->count(2)->create(['category_id' => $artigos->getKey()]);
        Post::factory()->publicado()->create(['category_id' => $episodios->getKey()]);

        $resposta = $this->get('/blog');
        $conteudo = $resposta->getContent();

        // Ordem do painel (`position`), nao ordem de criacao nem alfabetica.
        $this->assertLessThan(
            strpos($conteudo, 'categoria=episodios'),
            strpos($conteudo, 'categoria=artigos'),
        );
        $resposta->assertSee('Artigos <b>2</b>', false);
        $resposta->assertSee('Episódios <b>1</b>', false);
        $resposta->assertSee('Todos <b>3</b>', false);
        // Categoria sem post publicado nao vira aba.
        $resposta->assertDontSee('categoria=relatos-de-parto', false);
    }

    public function test_categoria_so_com_post_fora_do_ar_nao_vira_aba(): void
    {
        $categoria = Category::factory()->create(['name' => 'Materiais', 'slug' => 'materiais']);
        Post::factory()->create(['category_id' => $categoria->getKey()]);
        Post::factory()->arquivado()->create(['category_id' => $categoria->getKey()]);
        Post::factory()->agendado()->create(['category_id' => $categoria->getKey()]);
        Post::factory()->publicado()->create(['category_id' => $categoria->getKey()])->delete();

        $this->get('/blog')->assertDontSee('categoria=materiais', false);
    }

    public function test_post_sem_categoria_aparece_em_todos_e_nao_vira_aba(): void
    {
        Post::factory()->publicado()->create(['title' => 'Órfão de categoria', 'category_id' => null]);

        $resposta = $this->get('/blog');

        $resposta->assertSee('Órfão de categoria', false);
        $resposta->assertSee('Todos <b>1</b>', false);
        $resposta->assertDontSee('Sem categoria', false);
        $resposta->assertDontSee('class="bll__tab" href="/blog?categoria=', false);
    }

    public function test_a_aba_filtra_e_o_contador_mostra_o_recorte(): void
    {
        $artigos = Category::factory()->create(['name' => 'Artigos', 'slug' => 'artigos']);
        Post::factory()->publicado()->count(3)->create(['category_id' => $artigos->getKey()]);
        Post::factory()->publicado()->count(4)->create();

        $resposta = $this->get('/blog?categoria=artigos');

        $resposta->assertOk();
        $resposta->assertSee('3 de 7 posts', false);
        $this->assertSame(3, substr_count($resposta->getContent(), 'class="post-card"'));
    }

    // ── busca ────────────────────────────────────────────────────────

    public function test_a_busca_acha_por_titulo_descricao_e_conteudo(): void
    {
        Post::factory()->publicado()->create([
            'title' => 'Pré-eclâmpsia: mitos e verdades',
            'slug' => 'por-titulo',
            'description' => 'Nada de especial aqui.',
            'content' => PostFactory::conteudo('Nada de especial aqui.'),
        ]);
        Post::factory()->publicado()->create([
            'title' => 'Um título qualquer',
            'slug' => 'por-descricao',
            'description' => 'Tudo sobre pré-eclâmpsia na gestação.',
            'content' => PostFactory::conteudo('Nada de especial aqui.'),
        ]);
        Post::factory()->publicado()->create([
            'title' => 'Outro título qualquer',
            'slug' => 'por-conteudo',
            'description' => 'Nada de especial aqui.',
            // Acento dentro do JSON do editor: e o caso real do corpo do post.
            'content' => PostFactory::conteudo('A pré-eclâmpsia costuma aparecer depois das vinte semanas.'),
        ]);
        Post::factory()->publicado()->create(['title' => 'Assunto sem relação', 'slug' => 'fora-da-busca']);

        $resposta = $this->get('/blog?q=pr%C3%A9-ecl%C3%A2mpsia');

        $resposta->assertOk();
        $resposta->assertSee('/blog/por-titulo', false);
        $resposta->assertSee('/blog/por-descricao', false);
        $resposta->assertSee('/blog/por-conteudo', false);
        $resposta->assertDontSee('/blog/fora-da-busca', false);
        // O campo volta preenchido, para refinar em vez de redigitar.
        $resposta->assertSee('value="pré-eclâmpsia"', false);
    }

    public function test_a_busca_respeita_o_recorte_publico(): void
    {
        Post::factory()->publicado()->create(['title' => 'Alto risco no ar', 'slug' => 'no-ar']);
        Post::factory()->create(['title' => 'Alto risco em rascunho', 'slug' => 'rascunho']);
        Post::factory()->arquivado()->create(['title' => 'Alto risco arquivado', 'slug' => 'arquivado']);
        Post::factory()->agendado()->create(['title' => 'Alto risco agendado', 'slug' => 'agendado']);
        Post::factory()->publicado()->create(['title' => 'Alto risco na lixeira', 'slug' => 'lixeira'])->delete();

        $resposta = $this->get('/blog?q=alto+risco');

        $resposta->assertSee('Alto risco no ar', false);
        $resposta->assertSee('1 de 1 posts', false);
        foreach (['rascunho', 'arquivado', 'agendado', 'lixeira'] as $fora) {
            $resposta->assertDontSee("/blog/{$fora}", false);
        }
    }

    /** O `%` do visitante e texto, nao curinga que devolve o blog inteiro. */
    public function test_curinga_digitado_na_busca_e_texto_comum(): void
    {
        Post::factory()->publicado()->create(['title' => 'Gestação de alto risco', 'slug' => 'no-ar']);

        $resposta = $this->get('/blog?q=%25');

        $resposta->assertOk();
        $resposta->assertDontSee('/blog/no-ar', false);
    }

    public function test_busca_sem_resultado_mostra_o_termo_e_os_atalhos_das_categorias(): void
    {
        $artigos = Category::factory()->create(['name' => 'Artigos', 'slug' => 'artigos']);
        Post::factory()->publicado()->create(['category_id' => $artigos->getKey()]);

        $resposta = $this->get('/blog?q=amamenta%C3%A7%C3%A3o+livre+demanda');

        $resposta->assertOk();
        $resposta->assertSee('Não achamos nada para <b>&ldquo;amamentação livre demanda&rdquo;</b>.', false);
        $resposta->assertSee('Ver todos os posts', false);
        $resposta->assertSee('Perguntar direto', false);
        // Os atalhos sao as categorias do painel, nao termos escritos em codigo.
        $resposta->assertSee('<a href="/blog?categoria=artigos">Artigos</a>', false);
    }

    public function test_a_busca_nao_grava_nada_em_lugar_nenhum(): void
    {
        Post::factory()->publicado()->create(['title' => 'Gestação de alto risco']);

        $antes = $this->retratoDoBanco();

        $this->get('/blog?q=alto+risco')->assertOk();
        $this->get('/blog?q=nada+encontra+isso')->assertOk();
        $this->get('/blog?q=depress%C3%A3o+p%C3%B3s-parto&categoria=artigos&pagina=2')->assertOk();

        $this->assertSame($antes, $this->retratoDoBanco());
    }

    // ── post ─────────────────────────────────────────────────────────

    public function test_o_post_traz_capa_categoria_titulo_autor_data_e_aviso(): void
    {
        $categoria = Category::factory()->create(['name' => 'Episódios', 'slug' => 'episodios']);

        Post::factory()->publicado()->create([
            'title' => 'Ep. 12 — Pré-eclâmpsia: mitos e verdades',
            'slug' => 'pre-eclampsia-mitos-e-verdades',
            'category_id' => $categoria->getKey(),
            'author' => 'Dra. Marina Mariz',
            'published_at' => now()->setDate(2025, 1, 27)->setTime(12, 0),
            'image_id' => Media::factory()->create()->getKey(),
        ]);

        $resposta = $this->get('/blog/pre-eclampsia-mitos-e-verdades');

        $resposta->assertOk();
        $resposta->assertSee('<span class="pst__cat rise" data-d="1">Episódios</span>', false);
        $resposta->assertSee('id="post-titulo">Ep. 12 — Pré-eclâmpsia: mitos e verdades</h1>', false);
        $resposta->assertSee('<span>Dra. Marina Mariz</span>', false);
        $resposta->assertSee('<time datetime="2025-01-27">27 de janeiro de 2025</time>', false);
        $resposta->assertSee('class="pst__shot"', false);
        $resposta->assertSee('Este conteúdo é informativo e não substitui consulta médica. <a href="/contato">Marque sua consulta agora</a>.', false);
        $resposta->assertSee('<link rel="canonical" href="https://dramarinamariz.com.br/blog/pre-eclampsia-mitos-e-verdades">', false);
    }

    public function test_post_sem_categoria_nao_mostra_a_pilula(): void
    {
        Post::factory()->publicado()->create(['slug' => 'sem-categoria', 'category_id' => null]);

        $resposta = $this->get('/blog/sem-categoria');

        $resposta->assertOk();
        $resposta->assertDontSee('class="pst__cat', false);
        $resposta->assertDontSee('article:section', false);
    }

    public function test_o_post_publica_o_json_ld_de_blogposting_com_os_dados_do_post(): void
    {
        Post::factory()->publicado()->create([
            'title' => 'Ep. 12 — Pré-eclâmpsia: mitos e verdades',
            'slug' => 'pre-eclampsia-mitos-e-verdades',
            'description' => 'A pressão que sobe depois das vinte semanas assusta.',
            'author' => 'Dra. Marina Mariz',
            'published_at' => now()->setDate(2025, 1, 27)->setTime(12, 0),
        ]);

        $resposta = $this->get('/blog/pre-eclampsia-mitos-e-verdades');

        $bruto = $resposta->getContent();
        preg_match('#<script type="application/ld\+json">\s*(.+?)\s*</script>#s', $bruto, $achado);
        $dados = json_decode($achado[1] ?? '', true);

        // `@context` e tambem diretiva do Blade: se a view escrever a chave
        // solta num `{{ }}`, ela some do JSON-LD e o Google perde o schema.
        $this->assertSame('https://schema.org', $dados['@context'] ?? null);
        $this->assertSame('BlogPosting', $dados['@type'] ?? null);
        $this->assertSame('Ep. 12 — Pré-eclâmpsia: mitos e verdades', $dados['headline'] ?? null);
        $this->assertSame('A pressão que sobe depois das vinte semanas assusta.', $dados['description'] ?? null);
        $this->assertSame('2025-01-27', $dados['datePublished'] ?? null);
        $this->assertSame('Dra. Marina Mariz', $dados['author']['name'] ?? null);
        $this->assertSame(
            'https://dramarinamariz.com.br/blog/pre-eclampsia-mitos-e-verdades',
            $dados['mainEntityOfPage'] ?? null,
        );
    }

    // ── os quatro CTAs de fechamento ─────────────────────────────────

    public function test_o_cta_de_newsletter_renderiza_a_variacao_de_newsletter(): void
    {
        $resposta = $this->abrirComCta(PostCta::Newsletter);

        $resposta->assertSee('<aside class="art__cta close--news rise rise--right"', false);
        $resposta->assertSee('ph-envelope-simple', false);
        $resposta->assertSee('Quer conteúdo assim no seu e-mail?', false);
        // O formulario aponta para o endpoint real e leva os campos que o
        // Form Request exige; endereco literal nao provava que grava.
        $resposta->assertSee('class="close__form" method="post" action="'.route('formularios.newsletter').'"', false);
        $resposta->assertSee('name="aceita_newsletter"', false);
        $resposta->assertSee('name="ciencia_politica"', false);
        $resposta->assertSee('name="submission_id"', false);
        $this->assertOutrosCtasAusentes($resposta, 'Quer conteúdo assim no seu e-mail?');
    }

    public function test_o_cta_de_consulta_renderiza_a_variacao_de_consulta(): void
    {
        $resposta = $this->abrirComCta(PostCta::Consulta);

        $resposta->assertSee('ph-calendar-check', false);
        $resposta->assertSee('Quer conversar sobre o seu caso?', false);
        $resposta->assertSee('<a class="btn btn--primary" href="/contato"><span class="btn__label">Agendar consulta', false);
        $resposta->assertDontSee('close--news', false);
        $this->assertOutrosCtasAusentes($resposta, 'Quer conversar sobre o seu caso?');
    }

    public function test_o_cta_de_material_aponta_direto_para_o_arquivo_sem_modal_e_sem_lead(): void
    {
        $arquivo = Media::factory()->create(['original_name' => 'guia.pdf', 'mime_type' => 'application/pdf']);

        $resposta = $this->abrirComCta(PostCta::Material, [
            'material_description' => 'O guia reúne o calendário de consultas e os exames de cada trimestre.',
            'material_media_id' => $arquivo->getKey(),
        ]);

        $resposta->assertSee('ph-download-simple', false);
        $resposta->assertSee('Baixe o material completo', false);
        // A descricao deste CTA e a unica escrita no painel.
        $resposta->assertSee('O guia reúne o calendário de consultas e os exames de cada trimestre.', false);
        $resposta->assertSee('/download/'.$arquivo->getKey(), false);
        $resposta->assertSee('download><span class="btn__label">Baixar material gratuito', false);
        // Sem modal de nome e e-mail: este clique nao gera lead.
        $resposta->assertDontSee('close__form', false);
        $this->assertOutrosCtasAusentes($resposta, 'Baixe o material completo');
    }

    public function test_o_cta_de_podcast_renderiza_a_variacao_de_podcast(): void
    {
        $resposta = $this->abrirComCta(PostCta::Podcast, [
            'episode_url' => 'https://www.youtube.com/watch?v=abc123',
        ]);

        $resposta->assertSee('ph-microphone-stage', false);
        $resposta->assertSee('Esse assunto virou episódio', false);
        $resposta->assertSee('A conversa completa está no Sem Neura Podcast, com a Dra. Marina Mariz e a Dra. Carol Flores.', false);
        $resposta->assertSee('href="https://www.youtube.com/watch?v=abc123" target="_blank" rel="noopener"', false);
        $resposta->assertSee('Ouvir o episódio', false);
        $this->assertOutrosCtasAusentes($resposta, 'Esse assunto virou episódio');
    }

    // ── relacionados ─────────────────────────────────────────────────

    public function test_relacionados_excluem_arquivado_rascunho_e_lixeira(): void
    {
        $post = Post::factory()->publicado()->create(['slug' => 'principal']);

        $noAr = Post::factory()->publicado()->create(['title' => 'Relacionado no ar']);
        $rascunho = Post::factory()->create(['title' => 'Relacionado em rascunho']);
        $arquivado = Post::factory()->arquivado()->create(['title' => 'Relacionado arquivado']);
        $agendado = Post::factory()->agendado()->create(['title' => 'Relacionado agendado']);
        $lixeira = Post::factory()->publicado()->create(['title' => 'Relacionado na lixeira']);

        $post->relacionados()->sync([
            $noAr->getKey(), $rascunho->getKey(), $arquivado->getKey(), $agendado->getKey(), $lixeira->getKey(),
        ]);
        $lixeira->delete();

        $resposta = $this->get('/blog/principal');

        $resposta->assertOk();
        $resposta->assertSee('Continue lendo', false);
        $resposta->assertSee('Relacionado no ar', false);
        foreach (['em rascunho', 'arquivado', 'agendado', 'na lixeira'] as $fora) {
            $resposta->assertDontSee("Relacionado {$fora}", false);
        }
        $this->assertSame(1, substr_count($resposta->getContent(), 'class="post-card"'));
    }

    public function test_post_sem_relacionado_no_ar_nao_mostra_a_secao(): void
    {
        $post = Post::factory()->publicado()->create(['slug' => 'principal']);
        $post->relacionados()->sync([Post::factory()->arquivado()->create()->getKey()]);

        $this->get('/blog/principal')->assertDontSee('Continue lendo', false);
    }

    // ── conteudo rico ────────────────────────────────────────────────

    public function test_o_conteudo_rico_sai_sanitizado_na_pagina(): void
    {
        Post::factory()->publicado()->create([
            'slug' => 'conteudo-hostil',
            'content' => [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [[
                            'type' => 'text',
                            'text' => 'clique aqui',
                            'marks' => [['type' => 'link', 'attrs' => ['href' => 'javascript:alert(1)']]],
                        ]],
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => '<script>alert("xss")</script>']],
                    ],
                ],
            ],
        ]);

        $resposta = $this->get('/blog/conteudo-hostil');

        $resposta->assertOk();
        $resposta->assertSee('clique aqui', false);
        $resposta->assertDontSee('javascript:alert', false);
        $resposta->assertDontSee('<script>alert("xss")</script>', false);
        $resposta->assertSee('&lt;script&gt;', false);
    }

    // ── apoio ────────────────────────────────────────────────────────

    private function abrirComCta(PostCta $cta, array $extras = []): TestResponse
    {
        Post::factory()->publicado()->create([
            'slug' => 'post-com-cta',
            'closing_cta' => $cta,
            ...$extras,
        ]);

        return $this->get('/blog/post-com-cta')->assertOk();
    }

    private function assertOutrosCtasAusentes(TestResponse $resposta, string $oDaVez): void
    {
        $titulos = [
            'Quer conteúdo assim no seu e-mail?',
            'Quer conversar sobre o seu caso?',
            'Baixe o material completo',
            'Esse assunto virou episódio',
        ];

        foreach (array_diff($titulos, [$oDaVez]) as $outro) {
            $resposta->assertDontSee($outro, false);
        }

        // Um fecho por post, sempre.
        $this->assertSame(1, substr_count($resposta->getContent(), 'class="close__box"'));
    }

    /**
     * Contagem de linhas de todas as tabelas. Se a busca gravasse qualquer
     * coisa — termo, contador, log — este retrato mudaria.
     *
     * @return array<string, int>
     */
    private function retratoDoBanco(): array
    {
        $retrato = [];

        foreach (DB::select('SHOW TABLES') as $linha) {
            $tabela = array_values((array) $linha)[0];
            $retrato[$tabela] = DB::table($tabela)->count();
        }

        return $retrato;
    }
}
