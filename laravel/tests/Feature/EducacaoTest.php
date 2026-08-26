<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\CourseFormat;
use App\Enums\EducationType;
use App\Enums\EventFormat;
use App\Enums\MaterialFormat;
use App\Enums\TrainingModality;
use App\Enums\UserRole;
use App\Filament\Actions\VerNoSite;
use App\Filament\Resources\EducationItems\EducationItemResource;
use App\Filament\Resources\EducationItems\Pages\CreateEducationItem as CreateEducationItemPage;
use App\Filament\Resources\EducationItems\Pages\EditEducationItem as EditEducationItemPage;
use App\Filament\Resources\EducationItems\Pages\ListEducationItems;
use App\Http\Controllers\DownloadProtegidoController;
use App\Models\EducationItem;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class EducacaoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function usuario(UserRole $papel): User
    {
        $user = User::factory()->create(['role' => $papel]);
        $user->saveAppAuthenticationSecret('BASE32SECRETOTESTE');

        return $user;
    }

    private function pdf(): Media
    {
        $caminho = 'midia/guia-'.uniqid().'.pdf';
        Storage::disk('local')->put($caminho, '%PDF-1.4');

        return Media::factory()->create([
            'path' => $caminho,
            'original_name' => 'guia.pdf',
            'name' => 'Guia',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'width' => null,
            'height' => null,
        ]);
    }

    /** Item completo de cada tipo — todos os campos com * preenchidos. */
    private function completo(EducationType $tipo): array
    {
        $base = [
            'type' => $tipo,
            'title' => 'Item de teste',
            'description' => 'Descrição do item.',
            'status' => ContentStatus::Publicado,
        ];

        return match ($tipo) {
            EducationType::Livro => [...$base, 'external_url' => 'https://exemplo.com/livro'],
            EducationType::Ebook => [...$base, 'is_free' => false, 'external_url' => 'https://exemplo.com/ebook'],
            EducationType::Curso => [...$base, 'format' => CourseFormat::Gravado, 'external_url' => 'https://exemplo.com/curso'],
            EducationType::Evento => [...$base,
                'starts_at' => now()->addWeek(),
                'ends_at' => now()->addWeek()->addHours(3),
                'event_format' => EventFormat::Online,
            ],
            EducationType::Material => [...$base,
                'material_format' => MaterialFormat::Pdf,
                'file_id' => $this->pdf()->getKey(),
            ],
            EducationType::Formacao => [...$base,
                'format' => CourseFormat::Presencial,
                'modality' => TrainingModality::Mentoria,
                'external_url' => 'https://exemplo.com/formacao',
            ],
        };
    }

    // ── obrigatorio para publicar, nao para salvar ──────────────────

    public function test_rascunho_salva_so_com_o_titulo(): void
    {
        foreach (EducationType::cases() as $tipo) {
            $item = EducationItem::create(['type' => $tipo, 'title' => 'Ideia pela metade']);

            $this->assertTrue($item->exists);
            $this->assertSame(ContentStatus::Rascunho, $item->refresh()->status);
        }
    }

    public function test_publicar_exige_os_campos_do_tipo_escolhido(): void
    {
        foreach (EducationType::cases() as $tipo) {
            // Completo publica.
            $this->assertTrue(EducationItem::create($this->completo($tipo))->estaPublicado());

            // So com titulo e descricao, nao.
            try {
                EducationItem::create([
                    'type' => $tipo,
                    'title' => 'Sem o resto',
                    'description' => 'Descrição.',
                    'status' => ContentStatus::Publicado,
                ]);

                $this->fail("O tipo {$tipo->value} publicou sem os campos obrigatórios.");
            } catch (ValidationException $e) {
                $this->assertNotEmpty($e->errors());
            }
        }
    }

    public function test_publicar_sem_descricao_e_recusado(): void
    {
        $this->expectException(ValidationException::class);

        EducationItem::create([
            'type' => EducationType::Livro,
            'title' => 'Sem descrição',
            'external_url' => 'https://exemplo.com',
            'status' => ContentStatus::Publicado,
        ]);
    }

    public function test_rascunho_incompleto_nao_dispara_a_validacao_de_publicacao(): void
    {
        $item = EducationItem::create([
            'type' => EducationType::Evento,
            'title' => 'Evento a definir',
            'status' => ContentStatus::Rascunho,
        ]);

        $this->assertNull($item->starts_at);
    }

    // ── campo condicional so e exigido quando esta visivel ──────────

    public function test_evento_online_nao_cobra_local(): void
    {
        $item = EducationItem::create($this->completo(EducationType::Evento));

        $this->assertTrue($item->estaPublicado());
        $this->assertNull($item->venue_city);
    }

    public function test_evento_presencial_cobra_os_quatro_campos_de_local(): void
    {
        try {
            EducationItem::create([
                ...$this->completo(EducationType::Evento),
                'event_format' => EventFormat::Presencial,
            ]);

            $this->fail('Evento presencial publicou sem Local.');
        } catch (ValidationException $e) {
            $this->assertSame(
                ['venue_name', 'venue_address', 'venue_city', 'venue_state'],
                array_keys($e->errors()),
            );
        }

        $presencial = EducationItem::create([
            ...$this->completo(EducationType::Evento),
            'event_format' => EventFormat::Presencial,
            'venue_name' => 'Auditório',
            'venue_address' => 'Rua Um, 100',
            'venue_city' => 'Rio de Janeiro',
            'venue_state' => 'RJ',
        ]);

        $this->assertTrue($presencial->estaPublicado());
    }

    public function test_ebook_gratuito_nao_cobra_link_de_compra(): void
    {
        $item = EducationItem::create([
            ...$this->completo(EducationType::Ebook),
            'is_free' => true,
            'external_url' => null,
            'pdf_id' => $this->pdf()->getKey(),
        ]);

        $this->assertTrue($item->estaPublicado());
        $this->assertNull($item->external_url);
    }

    public function test_ebook_gratuito_cobra_o_pdf(): void
    {
        $this->expectException(ValidationException::class);

        EducationItem::create([
            ...$this->completo(EducationType::Ebook),
            'is_free' => true,
            'external_url' => null,
        ]);
    }

    public function test_ebook_pago_cobra_link_de_compra_e_nao_cobra_pdf(): void
    {
        try {
            EducationItem::create([...$this->completo(EducationType::Ebook), 'external_url' => null]);

            $this->fail('E-book pago publicou sem link de compra.');
        } catch (ValidationException $e) {
            $this->assertSame(['external_url'], array_keys($e->errors()));
        }
    }

    // ── e-book gratuito em Materiais Gratuitos ──────────────────────

    public function test_ebook_gratuito_aparece_em_materiais_gratuitos_com_os_padroes(): void
    {
        $pdf = $this->pdf();

        $ebook = EducationItem::create([
            ...$this->completo(EducationType::Ebook),
            'title' => 'E-book gratuito',
            'is_free' => true,
            'external_url' => null,
            'pdf_id' => $pdf->getKey(),
        ]);

        $material = EducationItem::create([...$this->completo(EducationType::Material), 'title' => 'Material']);

        $lista = EducationItem::paraSite(EducationType::Material)->pluck('id');

        $this->assertTrue($lista->contains($ebook->id));
        $this->assertTrue($lista->contains($material->id));

        $comoMaterial = $ebook->comoMaterial();

        $this->assertSame(MaterialFormat::Pdf, $comoMaterial['formato']);
        $this->assertSame($pdf->getKey(), $comoMaterial['arquivo']->getKey());
        $this->assertTrue($comoMaterial['exige_email']);
    }

    public function test_ebook_pago_nao_entra_em_materiais_gratuitos(): void
    {
        $ebook = EducationItem::create($this->completo(EducationType::Ebook));

        $this->assertFalse(
            EducationItem::paraSite(EducationType::Material)->pluck('id')->contains($ebook->id),
        );
    }

    // ── arquivo: o que entra no campo e conferido no servidor ───────

    /**
     * O campo do formulario e de envio, mas o estado que o Livewire manda e
     * so um id: quem decide se aquele registro serve para o campo e o
     * servidor, na hora de virar chave estrangeira.
     */
    private function pdfDoEbook(mixed $estado): ?int
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        $titulo = 'E-book '.uniqid();

        $this->formularioDe(EducationType::Ebook)
            ->fillForm(['title' => $titulo, 'is_free' => true, 'pdf_id' => $estado])
            ->call('create')
            ->assertHasNoFormErrors();

        return EducationItem::where('title', $titulo)->sole()->pdf_id;
    }

    public function test_pdf_da_biblioteca_e_aceito_no_campo_de_pdf(): void
    {
        $pdf = $this->pdf();

        $this->assertSame($pdf->getKey(), $this->pdfDoEbook([$pdf->getKey()]));
    }

    public function test_arquivo_de_outro_tipo_nao_entra_no_campo_de_pdf(): void
    {
        $video = Media::factory()->create([
            'original_name' => 'aula.mp4',
            'mime_type' => 'video/mp4',
        ]);

        $this->assertNull($this->pdfDoEbook([$video->getKey()]));
    }

    public function test_id_forjado_nao_vira_chave_estrangeira(): void
    {
        $this->assertNull($this->pdfDoEbook(['midia/../../.env']));
        $this->assertNull($this->pdfDoEbook([999999]));
    }
    // ── download protegido ──────────────────────────────────────────

    public function test_link_de_download_e_assinado_e_vale_tres_dias(): void
    {
        $material = EducationItem::create($this->completo(EducationType::Material));

        $link = $material->linkDeDownload();

        $this->assertStringContainsString('signature=', (string) $link);
        $this->get($link)->assertOk();

        $expira = now()->addDays(DownloadProtegidoController::VALIDADE_EM_DIAS);

        $this->travelTo($expira->copy()->subMinute());
        $this->get($link)->assertOk();

        $this->travelTo($expira->copy()->addMinute());
        $this->get($link)->assertForbidden();
    }

    public function test_ebook_gratuito_baixa_o_proprio_pdf(): void
    {
        $pdf = $this->pdf();

        $ebook = EducationItem::create([
            ...$this->completo(EducationType::Ebook),
            'is_free' => true,
            'external_url' => null,
            'pdf_id' => $pdf->getKey(),
        ]);

        $this->get($ebook->linkDeDownload())
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    // ── papeis ──────────────────────────────────────────────────────

    public function test_editor_cria_edita_e_publica_mas_nao_arquiva_nem_exclui(): void
    {
        $editor = $this->usuario(UserRole::Editor);
        $item = EducationItem::create($this->completo(EducationType::Livro));

        $this->actingAs($editor);

        $this->assertTrue($editor->can('create', EducationItem::class));
        $this->assertTrue($editor->can('update', $item));
        $this->assertTrue($editor->can('reorder', EducationItem::class));
        $this->assertTrue($item->estaPublicado());

        $this->assertFalse($editor->can('arquivar', EducationItem::class));
        $this->assertFalse($editor->can('delete', $item));
        $this->assertFalse($editor->can('deleteAny', EducationItem::class));
        $this->assertFalse($editor->can('restore', $item));
        $this->assertFalse($editor->can('forceDelete', $item));
    }

    public function test_administrador_arquiva_exclui_e_restaura(): void
    {
        $admin = $this->usuario(UserRole::Administrator);
        $item = EducationItem::create($this->completo(EducationType::Livro));

        $this->actingAs($admin);

        foreach (['arquivar', 'delete', 'restore', 'forceDelete'] as $acao) {
            $this->assertTrue($admin->can($acao, $item), "Administrador deveria poder {$acao}.");
        }

        foreach (['deleteAny', 'restoreAny', 'forceDeleteAny'] as $acao) {
            $this->assertTrue($admin->can($acao, EducationItem::class), "Administrador deveria poder {$acao}.");
        }
    }

    public function test_editor_nao_consegue_arquivar_pelo_formulario(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        $item = EducationItem::create($this->completo(EducationType::Livro));

        Livewire::test(EditEducationItemPage::class, ['record' => $item->getKey()])
            ->fillForm(['status' => ContentStatus::Arquivado->value])
            ->call('save')
            ->assertHasFormErrors(['status']);

        $this->assertTrue($item->fresh()->estaPublicado());
    }

    public function test_administrador_arquiva_pelo_formulario(): void
    {
        $this->actingAs($this->usuario(UserRole::Administrator));

        $item = EducationItem::create($this->completo(EducationType::Livro));

        Livewire::test(EditEducationItemPage::class, ['record' => $item->getKey()])
            ->fillForm(['status' => ContentStatus::Arquivado->value])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(ContentStatus::Arquivado, $item->fresh()->status);
    }

    // ── as 6 entradas do menu ───────────────────────────────────────

    public function test_o_menu_tem_uma_entrada_por_tipo_apontando_para_a_mesma_tela(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        $itens = EducationItemResource::getNavigationItems();

        $this->assertCount(6, $itens);

        foreach (EducationType::cases() as $i => $tipo) {
            $this->assertSame($tipo->plural(), $itens[$i]->getLabel());
            $this->assertStringContainsString('tipo='.$tipo->value, $itens[$i]->getUrl());
            $this->assertSame('Conteúdo', $itens[$i]->getGroup());
        }
    }

    public function test_cada_entrada_do_menu_lista_so_o_seu_tipo(): void
    {
        $this->actingAs($this->usuario(UserRole::Administrator));

        $itens = collect(EducationType::cases())->mapWithKeys(fn (EducationType $tipo) => [
            $tipo->value => EducationItem::create([
                ...$this->completo($tipo),
                'title' => 'Item de '.$tipo->value,
            ]),
        ]);

        foreach (EducationType::cases() as $tipo) {
            Livewire::withQueryParams(['tipo' => $tipo->value])
                ->test(ListEducationItems::class)
                ->assertCanSeeTableRecords([$itens[$tipo->value]])
                ->assertCanNotSeeTableRecords($itens->except($tipo->value)->values()->all());
        }
    }

    // ── escopo publico ──────────────────────────────────────────────

    public function test_rascunho_arquivado_e_lixeira_ficam_fora_do_site(): void
    {
        $publicado = EducationItem::create($this->completo(EducationType::Livro));
        $rascunho = EducationItem::create(['type' => EducationType::Livro, 'title' => 'Rascunho']);
        $arquivado = EducationItem::create([...$this->completo(EducationType::Livro), 'title' => 'Arquivado']);
        $arquivado->update(['status' => ContentStatus::Arquivado]);

        $naLixeira = EducationItem::create([...$this->completo(EducationType::Livro), 'title' => 'Na lixeira']);
        $naLixeira->delete();

        $ids = EducationItem::paraSite(EducationType::Livro)->pluck('id');

        $this->assertSame([$publicado->id], $ids->all());
        $this->assertFalse($ids->contains($rascunho->id));
        $this->assertFalse($ids->contains($arquivado->id));
        $this->assertFalse($ids->contains($naLixeira->id));
    }

    public function test_o_site_lista_na_ordem_do_painel_e_evento_por_data(): void
    {
        $segundo = EducationItem::create([...$this->completo(EducationType::Livro), 'title' => 'Segundo', 'position' => 2]);
        $primeiro = EducationItem::create([...$this->completo(EducationType::Livro), 'title' => 'Primeiro', 'position' => 1]);

        $this->assertSame(
            [$primeiro->id, $segundo->id],
            EducationItem::paraSite(EducationType::Livro)->pluck('id')->all(),
        );

        $depois = EducationItem::create([...$this->completo(EducationType::Evento), 'title' => 'Depois', 'starts_at' => now()->addMonth()]);
        $antes = EducationItem::create([...$this->completo(EducationType::Evento), 'title' => 'Antes', 'starts_at' => now()->addDay()]);

        $this->assertSame(
            [$antes->id, $depois->id],
            EducationItem::paraSite(EducationType::Evento)->pluck('id')->all(),
        );
    }

    // ── o formulario reativo ────────────────────────────────────────

    private function formularioDe(EducationType $tipo): Testable
    {
        return Livewire::withQueryParams(['tipo' => $tipo->value])
            ->test(CreateEducationItemPage::class);
    }

    public function test_a_tela_de_criar_ja_vem_com_o_tipo_da_entrada_do_menu(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        foreach (EducationType::cases() as $tipo) {
            $this->formularioDe($tipo)->assertFormSet(['type' => $tipo]);
        }
    }

    public function test_ebook_gratuito_troca_o_link_de_compra_pelos_arquivos(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        $this->formularioDe(EducationType::Ebook)
            ->assertFormFieldHidden('pdf_id')
            ->assertFormFieldHidden('epub_id')
            ->fillForm(['is_free' => true])
            ->assertFormFieldVisible('pdf_id')
            ->assertFormFieldVisible('epub_id');
    }

    public function test_publicar_ebook_gratuito_pelo_formulario_nao_cobra_link_de_compra(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        $this->formularioDe(EducationType::Ebook)
            ->fillForm([
                'title' => 'E-book gratuito',
                'description' => 'Descrição.',
                'is_free' => true,
                // Campo de envio: o estado e sempre uma lista de arquivos.
                'pdf_id' => [$this->pdf()->getKey()],
                'status' => ContentStatus::Publicado->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(EducationItem::where('title', 'E-book gratuito')->sole()->estaPublicado());
    }

    public function test_o_botao_publicar_publica_sem_mexer_no_select_de_status(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        $this->formularioDe(EducationType::Ebook)
            ->fillForm([
                'title' => 'E-book publicado no botão',
                'description' => 'Descrição.',
                'is_free' => true,
                'pdf_id' => [$this->pdf()->getKey()],
            ])
            ->call('publicarRegistro')
            ->assertHasNoFormErrors();

        $this->assertTrue(EducationItem::where('title', 'E-book publicado no botão')->sole()->estaPublicado());
    }

    public function test_o_botao_salvar_como_rascunho_guarda_pela_metade(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        $this->formularioDe(EducationType::Ebook)
            ->fillForm(['title' => 'Ideia pela metade'])
            ->call('salvarRascunho')
            ->assertHasNoFormErrors();

        $this->assertSame(
            ContentStatus::Rascunho,
            EducationItem::where('title', 'Ideia pela metade')->sole()->status,
        );
    }

    public function test_publicar_ebook_pago_pelo_formulario_cobra_link_de_compra(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        $this->formularioDe(EducationType::Ebook)
            ->fillForm([
                'title' => 'E-book pago',
                'description' => 'Descrição.',
                'is_free' => false,
                'status' => ContentStatus::Publicado->value,
            ])
            ->call('create')
            ->assertHasFormErrors(['external_url']);
    }

    public function test_evento_online_esconde_o_local_e_nao_o_cobra(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        $this->formularioDe(EducationType::Evento)
            ->fillForm([
                'title' => 'Encontro online',
                'description' => 'Descrição.',
                'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addWeek()->addHours(2)->format('Y-m-d H:i:s'),
                'event_format' => EventFormat::Online->value,
                'status' => ContentStatus::Publicado->value,
            ])
            ->assertFormFieldHidden('venue_city')
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(EducationItem::where('title', 'Encontro online')->sole()->estaPublicado());
    }

    public function test_evento_presencial_mostra_o_local_e_o_cobra(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        $this->formularioDe(EducationType::Evento)
            ->fillForm([
                'title' => 'Encontro presencial',
                'description' => 'Descrição.',
                'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addWeek()->addHours(2)->format('Y-m-d H:i:s'),
                'event_format' => EventFormat::Presencial->value,
                'status' => ContentStatus::Publicado->value,
            ])
            ->assertFormFieldVisible('venue_city')
            ->call('create')
            ->assertHasFormErrors(['venue_name', 'venue_address', 'venue_city', 'venue_state']);
    }

    public function test_rascunho_pelo_formulario_precisa_so_do_titulo(): void
    {
        $this->actingAs($this->usuario(UserRole::Editor));

        $this->formularioDe(EducationType::Curso)
            ->fillForm(['title' => 'Curso pela metade'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('education_items', [
            'title' => 'Curso pela metade',
            'status' => ContentStatus::Rascunho->value,
        ]);
    }

    public function test_nenhum_tipo_tem_pagina_propria(): void
    {
        // Sem slug: nenhum item do catalogo tem URL individual (doc 01).
        $this->assertFalse(
            Schema::hasColumn('education_items', 'slug'),
        );
    }

    /** Visualizar leva a secao publica do tipo, e so quando o item esta no ar. */
    public function test_visualizar_so_aparece_para_item_publicado(): void
    {
        $publicado = EducationItem::create([
            'type' => EducationType::Ebook,
            'title' => 'No ar',
        ]);

        // `saveQuietly` pula a conferencia de publicacao do model: o que este
        // teste olha e a visibilidade do botao, nao o formulario.
        $publicado->forceFill(['status' => ContentStatus::Publicado])->saveQuietly();

        $rascunho = EducationItem::create([
            'type' => EducationType::Ebook,
            'title' => 'Rascunho',
        ]);

        $this->actingAs($this->usuario(UserRole::Editor));

        Livewire::test(ListEducationItems::class)
            ->assertTableActionVisible('verNoSite', $publicado)
            ->assertTableActionHidden('verNoSite', $rascunho);

        $this->assertSame(
            route('site.educacao.ebooks'),
            VerNoSite::itemDeEducacao()->record($publicado)->getUrl(),
        );
    }
}
