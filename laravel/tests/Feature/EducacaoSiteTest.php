<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\CourseFormat;
use App\Enums\EducationType;
use App\Enums\EventFormat;
use App\Enums\MaterialFormat;
use App\Enums\OrigemMensagem;
use App\Http\Controllers\DownloadProtegidoController;
use App\Models\EducationItem;
use App\Models\InscricaoNewsletter;
use App\Models\Lead;
use App\Models\LeadDownload;
use App\Models\Media;
use App\Models\Mensagem;
use App\Models\TextoLegal;
use App\Services\Site;
use Database\Seeders\TextosLegaisSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * As sete paginas publicas de Educacao e os quatro formularios do site.
 *
 * O que estes testes defendem: cada pagina lista so o que e dela, a lista
 * pagina sem JavaScript e sem numeros de pagina, e todo formulario grava
 * de verdade no modulo certo.
 */
class EducacaoSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config(['pessoas.ip_hash_key' => 'chave-de-teste', 'pessoas.politica_versao' => '2026-08-21']);
    }

    // ── as sete paginas ─────────────────────────────────────────────

    /** Endereco => titulo e canonical, como estavam no HTML de origem. */
    public static function paginas(): array
    {
        return [
            'educacao' => ['/educacao', 'Educação — Dra. Marina Mariz', 'https://dramarinamariz.com.br/educacao'],
            'livros' => ['/educacao/livros', 'Livros — Dra. Marina Mariz', 'https://dramarinamariz.com.br/educacao/livros'],
            'ebooks' => ['/educacao/ebooks', 'E-books — Dra. Marina Mariz', 'https://dramarinamariz.com.br/educacao/ebooks'],
            'cursos' => ['/educacao/cursos', 'Cursos — Dra. Marina Mariz', 'https://dramarinamariz.com.br/educacao/cursos'],
            'eventos' => ['/educacao/eventos', 'Eventos — Dra. Marina Mariz', 'https://dramarinamariz.com.br/educacao/eventos'],
            'materiais' => ['/educacao/materiais-gratuitos', 'Materiais gratuitos — Dra. Marina Mariz', 'https://dramarinamariz.com.br/educacao/materiais-gratuitos'],
            'formacao' => ['/educacao/formacao-profissional', 'Formação profissional — Dra. Marina Mariz', 'https://dramarinamariz.com.br/educacao/formacao-profissional'],
        ];
    }

    #[DataProvider('paginas')]
    public function test_pagina_responde_com_titulo_e_canonical(string $url, string $titulo, string $canonical): void
    {
        $resposta = $this->get($url);

        $resposta->assertOk();
        $resposta->assertSee("<title>{$titulo}</title>", false);
        $resposta->assertSee('<link rel="canonical" href="'.$canonical.'">', false);
    }

    public function test_cada_pagina_lista_so_o_seu_tipo_e_so_o_publicado(): void
    {
        $this->livro(['title' => 'Gestar sem neura']);
        $this->livro(['title' => 'Rascunho de livro', 'status' => ContentStatus::Rascunho]);
        $this->curso(['title' => 'Preparação para o parto']);

        $livros = $this->get('/educacao/livros');
        $livros->assertSee('Gestar sem neura');
        $livros->assertDontSee('Rascunho de livro');
        $livros->assertDontSee('Preparação para o parto');

        $this->get('/educacao/cursos')
            ->assertSee('Preparação para o parto')
            ->assertDontSee('Gestar sem neura');
    }

    public function test_lista_segue_a_ordem_do_painel(): void
    {
        $this->livro(['title' => 'Terceiro', 'position' => 2]);
        $this->livro(['title' => 'Primeiro', 'position' => 0]);
        $this->livro(['title' => 'Segundo', 'position' => 1]);

        $this->get('/educacao/livros')->assertSeeInOrder(['Primeiro', 'Segundo', 'Terceiro']);
    }

    public function test_eventos_saem_por_data_e_nao_pela_ordem_do_painel(): void
    {
        $this->evento(['title' => 'Roda em novembro', 'position' => 0, 'starts_at' => now()->addMonths(3)]);
        $this->evento(['title' => 'Aula em setembro', 'position' => 9, 'starts_at' => now()->addMonth()]);

        $this->get('/educacao/eventos')->assertSeeInOrder(['Aula em setembro', 'Roda em novembro']);
    }

    #[DataProvider('paginas')]
    public function test_pagina_sem_item_mostra_o_estado_vazio(string $url): void
    {
        if ($url === '/educacao') {
            $this->markTestSkipped('O hub não tem lista própria.');
        }

        $resposta = $this->get($url);

        $resposta->assertSee('Em breve');
        $resposta->assertSee('lvl__empty', false);
        // Nunca uma grade em branco.
        $resposta->assertDontSee('<ol class="lvl__grid">', false);
    }

    public function test_pagina_dois_funciona_sem_javascript_e_sem_numeros_de_pagina(): void
    {
        foreach (range(1, 8) as $i) {
            $this->livro(['title' => "Livro {$i}", 'position' => $i]);
        }

        $primeira = $this->get('/educacao/livros');
        $primeira->assertSee('Livro 6');
        $primeira->assertDontSee('Livro 7');
        // O caminho sem JavaScript e um link comum para ?pagina=2.
        $primeira->assertSee('href="http://localhost/educacao/livros?pagina=2"', false);
        $primeira->assertSee('Carregar mais');
        // Nenhuma pagina de Educacao mostra numeros de pagina.
        $primeira->assertDontSee('class="pager"', false);

        $segunda = $this->get('/educacao/livros?pagina=2');
        $segunda->assertOk();
        $segunda->assertSee('Livro 7');
        $segunda->assertSee('Livro 8');
        $segunda->assertDontSee('Livro 1<');
        $segunda->assertDontSee('class="pager"', false);
        // Ultima leva: nao ha proxima, entao o bloco de carregar some.
        $segunda->assertDontSee('data-infinito', false);
    }

    public function test_ate_seis_itens_a_lista_nao_tem_bloco_de_carregar_mais(): void
    {
        foreach (range(1, 6) as $i) {
            $this->livro(['title' => "Livro {$i}", 'position' => $i]);
        }

        $this->get('/educacao/livros')
            ->assertDontSee('data-infinito', false)
            ->assertDontSee('Carregar mais');
    }

    public function test_ebook_gratuito_aparece_tambem_em_materiais_gratuitos_com_os_padroes_certos(): void
    {
        $ebook = $this->ebookGratuito(['title' => 'Checklist do pré-natal completo']);

        $this->get('/educacao/ebooks')->assertSee('Checklist do pré-natal completo');

        $materiais = $this->get('/educacao/materiais-gratuitos');
        $materiais->assertSee('Checklist do pré-natal completo');
        // Formato = PDF, Arquivo = o .pdf enviado, Exige e-mail = sim.
        $materiais->assertSee('>PDF</span>', false);
        $materiais->assertSee('name="media_id" value="'.$ebook->pdf_id.'"', false);

        $como = $ebook->comoMaterial();
        $this->assertSame(MaterialFormat::Pdf, $como['formato']);
        $this->assertSame($ebook->pdf_id, $como['arquivo']->getKey());
        $this->assertTrue($como['exige_email']);
    }

    public function test_evento_com_data_de_fim_no_passado_sai_dos_proximos(): void
    {
        $this->evento([
            'title' => 'Encontro que já aconteceu',
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subMonths(2)->addHours(2),
        ]);
        $this->evento(['title' => 'Roda que ainda vem', 'starts_at' => now()->addMonth()]);

        $resposta = $this->get('/educacao/eventos');

        $resposta->assertSee('Já aconteceram');
        // O passado sai de "proximos" e entra em "ja aconteceram" sozinho:
        // e a data de fim que move o card, nao alguem no painel.
        $resposta->assertSeeInOrder(['Roda que ainda vem', 'Já aconteceram', 'Encontro que já aconteceu']);
    }

    // ── os quatro formularios ───────────────────────────────────────

    public function test_formulario_de_contato_grava_mensagem(): void
    {
        $this->get('/contato')->assertSee('action="'.route('formularios.contato').'"', false);

        $this->from('/contato')->post(route('formularios.contato'), [
            'submission_id' => (string) Str::uuid(),
            'form' => 'contato',
            'nome' => 'Ana Souza',
            'email' => 'ana@example.com',
            'whatsapp' => '(31) 99999-0000',
            'texto' => 'Gostaria de marcar uma consulta.',
            'ciencia_politica' => '1',
        ])->assertRedirect('/contato')->assertSessionHas('form', 'contato');

        $mensagem = Mensagem::sole();
        $this->assertSame(OrigemMensagem::Contato, $mensagem->origem);
        $this->assertSame('Gostaria de marcar uma consulta.', $mensagem->texto);

        // O recibo aparece no lugar do formulario e recebe o foco.
        $this->get('/contato')->assertSee('Mensagem enviada. Respondemos em até 1 dia útil.');
    }

    public function test_formulario_de_formacao_profissional_grava_mensagem_com_a_origem_certa(): void
    {
        $this->get('/educacao/formacao-profissional')->assertSee('action="'.route('formularios.formacao').'"', false);

        $this->from('/educacao/formacao-profissional')->post(route('formularios.formacao'), [
            'submission_id' => (string) Str::uuid(),
            'form' => 'interesse-formacao',
            'nome' => 'Bia Lima',
            'email' => 'bia@example.com',
            'whatsapp' => '(31) 98888-0000',
            'texto' => 'Queremos a formação para a equipe.',
            'profissao' => 'Enfermeira obstetra',
            'instituicao' => 'Hospital Exemplo',
            'modalidade' => 'in-company',
            'ciencia_politica' => '1',
        ])->assertRedirect('/educacao/formacao-profissional');

        $mensagem = Mensagem::sole();
        $this->assertSame(OrigemMensagem::FormacaoProfissional, $mensagem->origem);
        $this->assertSame('in-company', $mensagem->modalidade);
        // CRM/COREN e opcional na tela — e opcional tambem no servidor.
        $this->assertNull($mensagem->registro_profissional);
    }

    public function test_formulario_de_material_grava_lead_e_devolve_link_de_tres_dias(): void
    {
        $material = $this->material();

        $this->get('/educacao/materiais-gratuitos')
            ->assertSee('action="'.route('formularios.material').'"', false);

        $resposta = $this->from('/educacao/materiais-gratuitos')->post(route('formularios.material'), [
            'submission_id' => (string) Str::uuid(),
            'form' => 'material-'.$material->getKey(),
            'nome' => 'Cris Dias',
            'email' => 'cris@example.com',
            'media_id' => $material->file_id,
            'ciencia_politica' => '1',
        ]);

        $resposta->assertRedirect('/educacao/materiais-gratuitos');
        $this->assertSame(1, Lead::count());
        $this->assertSame(1, LeadDownload::count());

        $link = session('download_url');
        $this->assertNotNull($link);
        $this->get($link)->assertOk();

        // Vale 3 dias, e nem um minuto a mais.
        $this->travel(DownloadProtegidoController::VALIDADE_EM_DIAS)->days();
        $this->get($link)->assertOk();
        $this->travel(2)->minutes();
        $this->get($link)->assertForbidden();
    }

    public function test_formulario_de_newsletter_grava_inscricao(): void
    {
        $this->get('/educacao/livros')->assertSee('action="'.route('formularios.newsletter').'"', false);

        $this->from('/educacao/livros')->post(route('formularios.newsletter'), [
            'submission_id' => (string) Str::uuid(),
            'form' => 'newsletter-livros',
            'nome' => 'Dani Reis',
            'email' => 'dani@example.com',
            'origem' => 'livros',
            'ciencia_politica' => '1',
            'aceita_newsletter' => '1',
        ])->assertRedirect('/educacao/livros');

        $inscricao = InscricaoNewsletter::sole();
        $this->assertSame('livros', $inscricao->origem);
        $this->assertSame(1, $inscricao->consentimentos()->count());

        $this->get('/educacao/livros')->assertSee('Pronto! Você vai receber as novidades no seu e-mail.');
    }

    public function test_toda_pagina_de_educacao_oferece_a_newsletter_com_a_sua_origem(): void
    {
        foreach (['livros', 'ebooks', 'cursos', 'eventos', 'materiais-gratuitos', 'formacao-profissional'] as $secao) {
            $this->get("/educacao/{$secao}")
                ->assertOk()
                ->assertSee('name="aceita_newsletter"', false)
                // Chave de idempotencia e protecao contra CSRF em todo envio.
                ->assertSee('name="submission_id"', false)
                ->assertSee('name="_token"', false);
        }
    }

    public function test_sem_ciencia_da_politica_o_formulario_operacional_e_recusado(): void
    {
        $this->from('/contato')->post(route('formularios.contato'), [
            'submission_id' => (string) Str::uuid(),
            'form' => 'contato',
            'nome' => 'Ana Souza',
            'email' => 'ana@example.com',
            'texto' => 'Gostaria de marcar uma consulta.',
        ])->assertRedirect('/contato')->assertSessionHasErrors('ciencia_politica');

        $this->assertSame(0, Mensagem::count());
    }

    public function test_erro_de_validacao_volta_ligado_ao_campo_e_anunciado(): void
    {
        $this->from('/contato')->post(route('formularios.contato'), [
            'submission_id' => (string) Str::uuid(),
            'form' => 'contato',
            'nome' => 'Ana Souza',
            'email' => 'nao-e-email',
            'texto' => 'Gostaria de marcar uma consulta.',
            'ciencia_politica' => '1',
        ])->assertRedirect('/contato');

        $resposta = $this->get('/contato');

        // O aviso nasce ao lado do campo que falhou, com aria-invalid,
        // aria-describedby, role=alert e o foco indo para ele.
        $resposta->assertSee('field--erro', false);
        $resposta->assertSee('aria-invalid="true" aria-describedby="ct-email-erro"', false);
        $resposta->assertSee('autofocus', false);
        $resposta->assertSee('<span class="field__erro" id="ct-email-erro" role="alert">', false);
        // O erro nunca apaga o que foi digitado.
        $resposta->assertSee('value="Ana Souza"', false);
    }

    public function test_capa_do_item_vem_da_biblioteca_de_midia(): void
    {
        $capa = Media::factory()->create(['path' => 'midia/capa.webp', 'alt' => 'Capa de Gestar sem neura']);
        Storage::disk('local')->put('midia/capa.webp', 'imagem');

        $this->livro(['title' => 'Gestar sem neura', 'image_id' => $capa->getKey()]);

        $this->get('/educacao/livros')->assertSee('alt="Capa de Gestar sem neura"', false);
    }

    public function test_mesma_submission_id_duas_vezes_nao_duplica(): void
    {
        $dados = [
            'submission_id' => (string) Str::uuid(),
            'form' => 'contato',
            'nome' => 'Ana Souza',
            'email' => 'ana@example.com',
            'texto' => 'Gostaria de marcar uma consulta.',
            'ciencia_politica' => '1',
        ];

        $this->from('/contato')->post(route('formularios.contato'), $dados);
        $this->from('/contato')->post(route('formularios.contato'), $dados);

        $this->assertSame(1, Mensagem::count());
    }

    // ── textos legais ───────────────────────────────────────────────

    /**
     * Pagina legal em branco e pior do que texto-padrao: o visitante tem
     * direito de saber o que e coletado, e a LGPD nao espera revisao. Por
     * isso o seeder ja publica os dois, com os dados que o cadastro tem.
     */
    public function test_seeder_publica_os_textos_legais_com_os_dados_do_cadastro(): void
    {
        $this->seed(TextosLegaisSeeder::class);

        foreach (TextoLegal::CHAVES as $chave => $titulo) {
            $texto = TextoLegal::where('chave', $chave)->sole();

            $this->assertSame(ContentStatus::Publicado, $texto->status);
            $this->assertNotEmpty($texto->conteudo);
            $this->assertSame($texto->conteudo, $texto->conteudo_publicado);
            $this->assertNotNull($texto->publicado_em);
            $this->assertNotNull(Site::textoLegal($chave));

            // Nada de lacuna editorial no ar: o texto sobe pronto de ler.
            $this->assertStringNotContainsString('leg__falta', $texto->conteudo);
            $this->assertStringNotContainsString('[CNPJ]', $texto->conteudo);
            $this->assertStringNotContainsString('[RAZÃO SOCIAL]', $texto->conteudo);
            // O Google Sheets saiu do projeto; a copia fora do sistema e o CSV.
            $this->assertStringNotContainsString('Google Sheets', $texto->conteudo);
        }

        $this->get('/politica-de-privacidade')
            ->assertOk()
            ->assertSee('O controlador dos dados pessoais tratados neste site')
            ->assertSee('CRM-MG sob o n')
            ->assertSee('30140-100');
        $this->get('/termos-de-uso')
            ->assertOk()
            ->assertSee('Estes Termos regem o uso do site');
    }

    // ── fabricas ────────────────────────────────────────────────────

    private function livro(array $dados = []): EducationItem
    {
        return EducationItem::factory()->create($dados + [
            'type' => EducationType::Livro,
            'description' => 'Descrição do livro.',
            'external_url' => 'https://exemplo.com.br/livro',
            'status' => ContentStatus::Publicado,
        ]);
    }

    private function curso(array $dados = []): EducationItem
    {
        return EducationItem::factory()->create($dados + [
            'type' => EducationType::Curso,
            'description' => 'Descrição do curso.',
            'format' => CourseFormat::OnlineAoVivo,
            'external_url' => 'https://exemplo.com.br/curso',
            'status' => ContentStatus::Publicado,
        ]);
    }

    private function evento(array $dados = []): EducationItem
    {
        $inicio = $dados['starts_at'] ?? now()->addMonth();

        return EducationItem::factory()->create($dados + [
            'type' => EducationType::Evento,
            'description' => 'Descrição do evento.',
            'starts_at' => $inicio,
            'ends_at' => $inicio->copy()->addHours(2),
            'event_format' => EventFormat::Online,
            'status' => ContentStatus::Publicado,
        ]);
    }

    private function material(array $dados = []): EducationItem
    {
        return EducationItem::factory()->create($dados + [
            'type' => EducationType::Material,
            'title' => 'Guia do pré-natal de alto risco',
            'description' => 'Descrição do material.',
            'material_format' => MaterialFormat::Pdf,
            'file_id' => $this->arquivo()->getKey(),
            'requires_email' => true,
            'status' => ContentStatus::Publicado,
        ]);
    }

    private function ebookGratuito(array $dados = []): EducationItem
    {
        return EducationItem::factory()->create($dados + [
            'type' => EducationType::Ebook,
            'description' => 'Descrição do e-book.',
            'is_free' => true,
            'pdf_id' => $this->arquivo()->getKey(),
            'status' => ContentStatus::Publicado,
        ]);
    }

    private function arquivo(): Media
    {
        $caminho = 'midia/guia-'.uniqid().'.pdf';
        Storage::disk('local')->put($caminho, '%PDF-1.4');

        return Media::factory()->create([
            'path' => $caminho,
            'original_name' => 'guia.pdf',
            'name' => 'Guia do pré-natal',
            'mime_type' => 'application/pdf',
            'size' => 2_516_582,
            'width' => null,
            'height' => null,
        ]);
    }
}
