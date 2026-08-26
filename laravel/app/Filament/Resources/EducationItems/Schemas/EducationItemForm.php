<?php

namespace App\Filament\Resources\EducationItems\Schemas;

use App\Enums\ContentStatus;
use App\Enums\CourseFormat;
use App\Enums\EducationType;
use App\Enums\EventFormat;
use App\Enums\MaterialFormat;
use App\Enums\TrainingModality;
use App\Filament\Schemas\AcoesDoFormulario;
use App\Filament\Schemas\CampoDeLink;
use App\Filament\Schemas\CamposDeSeo;
use App\Filament\Schemas\EnvioDeMidia;
use App\Models\EducationItem;
use App\Support\Publicacao;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;

/**
 * Um formulario so, reativo: o tipo escolhido decide quais campos aparecem
 * e quais sao exigidos para publicar (doc 01).
 */
class EducationItemForm
{
    /**
     * Duas colunas, como a tela de edicao do WordPress: os campos do item na
     * coluna larga da esquerda, publicacao e SEO na faixa estreita da direita.
     * A largura da faixa e o empilhamento em tela estreita vem do tema.
     */
    public static function configure(Schema $schema): Schema
    {
        $temTipo = fn (Get $get): bool => filled($get('type'));

        return $schema
            ->columns(2)
            ->extraAttributes(['class' => 'fi-wp-edicao'])
            ->components([
                // A coluna da esquerda fica no lugar mesmo vazia, senao a
                // caixa da direita escorregaria para a coluna larga enquanto
                // o tipo nao foi escolhido.
                Group::make([
                    self::comuns()->visible($temTipo),
                    self::livro(),
                    self::ebook(),
                    self::cursoEFormacao(),
                    self::evento(),
                    self::material(),
                ]),

                Group::make([
                    self::publicacao($temTipo),
                    CamposDeSeo::secao()->visible($temTipo),
                ]),
            ]);
    }

    // ── blocos ──────────────────────────────────────────────────────

    private static function comuns(): Section
    {
        return Section::make('Item')
            ->columns(2)
            ->schema([
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Descrição')
                    ->rows(5)
                    ->required(Publicacao::exigido())
                    ->helperText('Texto que aparece na ficha do item.')
                    ->columnSpanFull(),

                EnvioDeMidia::imagem('image_id', 'Imagem')
                    ->helperText('Proporção sugerida 3:2, largura de 1200px. O navegador converte para WebP e o arquivo entra na Biblioteca de Mídia.')
                    ->columnSpanFull(),
            ]);
    }

    private static function livro(): Section
    {
        return Section::make('Livro')
            ->columns(2)
            ->visible(fn (Get $get): bool => self::eTipo($get, EducationType::Livro))
            ->schema([
                TextInput::make('year')
                    ->label('Ano')
                    ->numeric()
                    ->minValue(1900)
                    ->maxValue((int) date('Y') + 5),

                TextInput::make('publisher')
                    ->label('Editora')
                    ->maxLength(255),

                self::link('external_url', 'Link de compra')
                    ->required(Publicacao::exigido())
                    ->columnSpanFull(),
            ]);
    }

    private static function ebook(): Section
    {
        $gratuito = fn (Get $get): bool => (bool) $get('is_free');

        return Section::make('E-book')
            ->columns(2)
            ->visible(fn (Get $get): bool => self::eTipo($get, EducationType::Ebook))
            ->schema([
                Toggle::make('is_free')
                    ->label('É gratuito?')
                    ->live()
                    ->helperText('Gratuito: o e-book passa a aparecer também em Materiais gratuitos, com o .pdf enviado aqui e pedindo e-mail para baixar.')
                    ->columnSpanFull(),

                // Gratuito troca o link de compra pelos arquivos, e vice-versa.
                self::link('external_url', 'Link de compra')
                    ->required(Publicacao::exigidoQuando(fn (Get $get): bool => ! $get('is_free')))
                    ->visible(fn (Get $get): bool => ! $gratuito($get))
                    ->columnSpanFull(),

                EnvioDeMidia::arquivo('pdf_id', 'Arquivo .pdf', ['pdf'])
                    ->required(Publicacao::exigidoQuando($gratuito))
                    ->visible($gratuito),

                EnvioDeMidia::arquivo('epub_id', 'Arquivo .epub', ['epub'])
                    ->helperText('Opcional. Até 50MB. Entra na Biblioteca de Mídia.')
                    ->visible($gratuito),
            ]);
    }

    private static function cursoEFormacao(): Section
    {
        $formacao = fn (Get $get): bool => self::eTipo($get, EducationType::Formacao);

        return Section::make('Detalhes')
            ->columns(2)
            ->visible(fn (Get $get): bool => self::eTipo($get, EducationType::Curso, EducationType::Formacao))
            ->schema([
                Textarea::make('audience')
                    ->label('Para quem é')
                    ->rows(2)
                    ->visible($formacao)
                    ->columnSpanFull(),

                Select::make('format')
                    ->label('Formato')
                    ->options(CourseFormat::class)
                    ->required(Publicacao::exigido()),

                Select::make('modality')
                    ->label('Modalidade')
                    ->options(TrainingModality::class)
                    ->required(Publicacao::exigidoQuando($formacao))
                    ->visible($formacao),

                TextInput::make('workload')
                    ->label('Carga horária')
                    ->maxLength(60)
                    ->placeholder('ex.: 40 horas'),

                TextInput::make('seats')
                    ->label('Vagas')
                    ->numeric()
                    ->minValue(0),

                DatePicker::make('starts_at')
                    ->label('Data de início')
                    ->displayFormat('d/m/Y'),

                DatePicker::make('ends_at')
                    ->label('Data de fim')
                    ->displayFormat('d/m/Y'),

                self::link('external_url', 'Link externo')
                    ->required(Publicacao::exigido())
                    ->helperText('Página na plataforma; é para onde o botão leva, em nova aba.')
                    ->columnSpanFull(),
            ]);
    }

    private static function evento(): Section
    {
        $presencial = fn (Get $get): bool => self::valor($get('event_format')) === EventFormat::Presencial->value;

        return Section::make('Evento')
            ->columns(2)
            ->visible(fn (Get $get): bool => self::eTipo($get, EducationType::Evento))
            ->schema([
                DateTimePicker::make('starts_at')
                    ->label('Data e hora de início')
                    ->seconds(false)
                    ->displayFormat('d/m/Y H:i')
                    ->required(Publicacao::exigido()),

                DateTimePicker::make('ends_at')
                    ->label('Data e hora de fim')
                    ->seconds(false)
                    ->displayFormat('d/m/Y H:i')
                    ->required(Publicacao::exigido())
                    ->helperText('O site move o evento para "Já aconteceram" sozinho quando esta data passa.'),

                Select::make('event_format')
                    ->label('Formato')
                    ->options(EventFormat::class)
                    ->live()
                    ->required(Publicacao::exigido())
                    ->columnSpanFull(),

                // Evento online nao tem local, entao o bloco some e nada dele
                // e cobrado (doc 01).
                Fieldset::make('Local')
                    ->columns(2)
                    ->visible($presencial)
                    ->schema([
                        TextInput::make('venue_name')
                            ->label('Nome')
                            ->maxLength(255)
                            ->required(Publicacao::exigidoQuando($presencial)),

                        TextInput::make('venue_address')
                            ->label('Endereço')
                            ->maxLength(255)
                            ->required(Publicacao::exigidoQuando($presencial)),

                        TextInput::make('venue_city')
                            ->label('Cidade')
                            ->maxLength(255)
                            ->required(Publicacao::exigidoQuando($presencial)),

                        TextInput::make('venue_state')
                            ->label('Estado')
                            ->maxLength(2)
                            ->placeholder('UF')
                            ->required(Publicacao::exigidoQuando($presencial)),
                    ]),

                self::link('external_url', 'Link de inscrição')
                    ->columnSpanFull(),
            ]);
    }

    private static function material(): Section
    {
        return Section::make('Material gratuito')
            ->columns(2)
            ->visible(fn (Get $get): bool => self::eTipo($get, EducationType::Material))
            ->schema([
                Select::make('material_format')
                    ->label('Formato')
                    ->options(MaterialFormat::class)
                    ->required(Publicacao::exigido()),

                EnvioDeMidia::arquivo('file_id', 'Arquivo', ['pdf', 'epub', 'csv', 'xlsx', 'mp4', 'webm', 'mp3', 'm4a', 'wav'])
                    ->required(Publicacao::exigido())
                    ->helperText('PDF, EPUB, CSV, XLSX, MP4, WebM, MP3, M4A ou WAV, até 50MB. Entra na Biblioteca de Mídia.'),

                Toggle::make('requires_email')
                    ->label('Exige e-mail para baixar')
                    ->default(true)
                    ->helperText('O arquivo é servido por link com validade de 3 dias.')
                    ->columnSpanFull(),

                TextInput::make('lead_source')
                    ->label('Nome da origem do lead')
                    ->maxLength(255)
                    ->placeholder('ex.: Guia do pré-natal')
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Caixa "Publicar" do WordPress, no topo da coluna da direita: o tipo,
     * que decide o formulario inteiro, o status e os mesmos botoes do rodape.
     */
    private static function publicacao(Closure $temTipo): Section
    {
        return Section::make('Publicação')
            ->schema([
                // O tipo continua sendo a primeira decisao: cada tipo tem
                // campos diferentes e exigencias diferentes para publicar.
                Select::make('type')
                    ->label('Tipo')
                    ->options(EducationType::class)
                    ->required()
                    ->live()
                    ->default(fn (): ?string => request()->query('tipo'))
                    ->helperText('Os campos mudam conforme o tipo.'),

                Select::make('status')
                    ->label('Status')
                    ->options(ContentStatus::class)
                    ->default(ContentStatus::Rascunho->value)
                    ->selectablePlaceholder(false)
                    ->required()
                    ->live()
                    // Arquivar e so do Administrador; o Filament recusa no
                    // servidor a opcao desabilitada (doc 01).
                    ->disableOptionWhen(fn (string $value): bool => $value === ContentStatus::Arquivado->value
                        && ! Gate::allows('arquivar', EducationItem::class))
                    ->helperText(self::pendencias(...))
                    ->visible($temTipo),

                AcoesDoFormulario::noTopo(),
            ]);
    }

    /** Diz o que ainda falta em vez de so barrar o salvamento (doc 01). */
    private static function pendencias(Get $get): ?string
    {
        if (! Publicacao::vaiPublicar($get('status'))) {
            return 'Rascunho guarda a ideia pela metade: só o título é obrigatório.';
        }

        $dados = [];

        foreach (EducationItem::COLUNAS_EXIGIVEIS as $campo) {
            $dados[$campo] = $get($campo);
        }

        $faltando = Publicacao::pendencias(EducationItem::exigidos($dados), $dados);

        return $faltando === []
            ? null
            : 'Falta preencher para publicar: '.implode(', ', $faltando).'.';
    }

    // ── pecas repetidas ─────────────────────────────────────────────

    private static function link(string $campo, string $rotulo): TextInput
    {
        return TextInput::make($campo)
            ->label($rotulo)
            ->url()
            ->startsWith('https://')
            ->maxLength(255)
            ->placeholder('https://')
            ->extraInputAttributes(CampoDeLink::comecaEmHttps());
    }

    private static function eTipo(Get $get, EducationType ...$tipos): bool
    {
        $tipo = EducationType::tryFrom((string) self::valor($get('type')));

        return in_array($tipo, $tipos, strict: true);
    }

    private static function valor(mixed $estado): mixed
    {
        return $estado instanceof \BackedEnum ? $estado->value : $estado;
    }
}
