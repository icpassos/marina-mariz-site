<?php

namespace App\Filament\Pages;

use App\Enums\Icone;
use App\Filament\Schemas\CampoDeLink;
use App\Models\DadosDoSite;
use App\Models\TextoLegal;
use App\Services\Site;
use App\Support\Publicacao;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use UnitEnum;

/**
 * Contatos e dados (doc 03): uma area com abas, singleton, so do
 * Administrador. O que muda aqui muda em todos os pontos do site que
 * exibem esse dado, de uma vez.
 */
class ContatosEDados extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Icone::Phone;

    protected static string|UnitEnum|null $navigationGroup = 'Conteúdo';

    protected static ?string $navigationLabel = 'Contatos e dados';

    protected static ?string $title = 'Contatos e dados';

    protected static ?string $slug = 'contatos-e-dados';

    protected string $view = 'filament-panels::pages.page';

    /** De onde vem e para onde vai esta seção — a Marina não acompanhou a construção. */
    protected ?string $subheading = 'E-mail, telefone, WhatsApp, endereço, redes e textos legais que o site inteiro usa — rodapé, /contato, /politica-de-privacidade e /termos-de-uso. O que muda aqui muda em todos de uma vez.';

    /** @var array<string, mixed> */
    public array $data = [];

    /**
     * Pagina personalizada nao ganha Policy automatica: a regra do doc 00
     * ("Editor nao ve Contatos e dados") e escrita aqui e conferida de
     * novo em `save`.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->isAdministrator() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            ...DadosDoSite::instancia()->attributesToArray(),
            'textos' => TextoLegal::query()->get()
                ->mapWithKeys(fn (TextoLegal $texto): array => [
                    $texto->chave => [
                        'titulo' => $texto->titulo,
                        'conteudo' => $texto->conteudo,
                        'status' => $texto->status?->value,
                    ],
                ])
                ->all(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make()->tabs([
                    $this->abaContato(),
                    $this->abaIdentificacao(),
                    $this->abaEndereco(),
                    $this->abaRedes(),
                    ...array_map(
                        fn (string $chave): Tab => $this->abaTextoLegal($chave),
                        array_keys(TextoLegal::CHAVES),
                    ),
                ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Salvar alterações')
                            ->submit('save'),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        // A tela ja esconde o menu para o Editor, mas esconder botao nao
        // autoriza nada (doc 00): o papel e conferido de novo no servidor.
        abort_unless(static::canAccess(), 403);

        $dados = $this->form->getState();
        $textos = Arr::pull($dados, 'textos', []);

        DadosDoSite::instancia()->update($dados);

        foreach ($textos as $chave => $texto) {
            TextoLegal::query()->where('chave', $chave)->sole()->guardar($texto);
        }

        Notification::make()
            ->title('Alterações salvas')
            ->body('A mudança já está no ar.')
            ->success()
            ->send();
    }

    private function abaContato(): Tab
    {
        return Tab::make('Contato')->schema([
            TextInput::make('email')
                ->label('E-mail')
                ->email()
                ->required()
                ->maxLength(255),

            TextInput::make('telefone')
                ->label('Telefone')
                ->required()
                ->rule('regex:/^\d{10,11}$/')
                ->validationMessages(['regex' => 'Digite só os números, com DDD.'])
                ->helperText('Só números, com DDD. A tela formata e monta o link tel: sozinha.'),

            TextInput::make('whatsapp')
                ->label('WhatsApp')
                ->required()
                ->rule('regex:/^\d{10,11}$/')
                ->validationMessages(['regex' => 'Digite só os números, com DDD.'])
                ->helperText('Só números, com DDD. O link wa.me é montado com o +55 na frente.'),

            TextInput::make('horario_atendimento')
                ->label('Horário de atendimento')
                ->maxLength(255)
                ->helperText('Aparece na página de Contato.'),
        ]);
    }

    private function abaIdentificacao(): Tab
    {
        return Tab::make('Identificação profissional')->schema([
            TextInput::make('nome_divulgacao')
                ->label('Nome de divulgação')
                ->required()
                ->maxLength(255),

            TextInput::make('palavra_obrigatoria')
                ->label('Palavra obrigatória')
                ->required()
                ->maxLength(255)
                ->helperText('Forma literal exigida pela Resolução CFM vigente. Ex.: MÉDICO.'),

            TextInput::make('crm')
                ->label('CRM/UF e número')
                ->required()
                ->maxLength(255)
                ->helperText('Como deve aparecer no site. Ex.: CRM-MG 48.386.'),

            Repeater::make('especialidades')
                ->label('Especialidades e RQEs')
                ->required()
                ->minItems(1)
                ->addActionLabel('Adicionar especialidade')
                ->helperText('Cada RQE fica ao lado da sua especialidade. A correspondência é a cadastrada aqui, não a ordem dos números.')
                ->schema([
                    TextInput::make('especialidade')
                        ->label('Especialidade ou área registrada')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('rqe')
                        ->label('Número do RQE')
                        ->required()
                        ->maxLength(50),
                ])
                ->columns(2),
        ]);
    }

    private function abaEndereco(): Tab
    {
        return Tab::make('Endereço')->schema([
            TextInput::make('endereco_logradouro')
                ->label('Rua e número')
                ->required()
                ->maxLength(255),

            TextInput::make('endereco_complemento')
                ->label('Complemento')
                ->maxLength(255),

            TextInput::make('endereco_bairro')
                ->label('Bairro')
                ->required()
                ->maxLength(255),

            TextInput::make('endereco_cidade')
                ->label('Cidade')
                ->required()
                ->maxLength(255),

            TextInput::make('endereco_estado')
                ->label('Estado')
                ->required()
                ->rule('regex:/^[A-Z]{2}$/')
                ->validationMessages(['regex' => 'Use a sigla com duas letras maiúsculas. Ex.: MG.']),

            TextInput::make('endereco_cep')
                ->label('CEP')
                ->required()
                ->rule('regex:/^\d{8}$/')
                ->validationMessages(['regex' => 'Digite os 8 números do CEP, sem traço.']),
        ]);
    }

    private function abaRedes(): Tab
    {
        return Tab::make('Redes sociais')->schema(
            collect(DadosDoSite::REDES)
                ->map(fn (string $rotulo, string $chave): Group => Group::make(
                    // WhatsApp nao tem URL propria: o link vem do numero da
                    // aba Contato. Um numero so, um lugar so.
                    $chave === 'whatsapp'
                        ? [$this->interruptorDeRede($rotulo)]
                        : [
                            TextInput::make('url')
                                ->label($rotulo)
                                ->url()
                                ->rule('starts_with:https://')
                                ->validationMessages(['starts_with' => 'O endereço precisa começar com https://'])
                                ->extraInputAttributes(CampoDeLink::comecaEmHttps())
                                ->maxLength(255),
                            $this->interruptorDeRede($rotulo),
                        ]
                )->statePath("redes.{$chave}"))
                ->values()
                ->all()
        );
    }

    private function interruptorDeRede(string $rotulo): Toggle
    {
        return Toggle::make('visivel')
            ->label("Mostrar {$rotulo} no site")
            ->helperText('Desligado, some do site sem perder o link cadastrado.');
    }

    private function abaTextoLegal(string $chave): Tab
    {
        return Tab::make(TextoLegal::CHAVES[$chave])->schema([
            Group::make([
                TextInput::make('titulo')
                    ->label('Título')
                    ->maxLength(255),

                RichEditor::make('conteudo')
                    ->label('Conteúdo')
                    ->mergeTags(Site::rotulosDasVariaveis())
                    ->helperText('Use as variáveis do sistema para contato, endereço e identificação profissional — assim o texto acompanha o que for editado nas outras abas.')
                    ->required(Publicacao::exigido()),

                Select::make('status')
                    ->label('Status')
                    ->options(Site::statusDeTextoLegal())
                    ->default('rascunho')
                    ->required()
                    ->helperText('Salvar rascunho não muda a página pública. Publicar troca o texto em uso, sem histórico.'),

                Actions::make([
                    Action::make('visualizar')
                        ->label('Visualizar')
                        ->modalHeading(TextoLegal::CHAVES[$chave])
                        ->modalContent(fn (): Htmlable => $this->previa($chave))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Fechar'),
                ]),
            ])->statePath("textos.{$chave}"),
        ]);
    }

    /** Previa com as variaveis ja substituidas, como o site mostra. */
    private function previa(string $chave): Htmlable
    {
        return RichContentRenderer::make($this->data['textos'][$chave]['conteudo'] ?? '')
            ->mergeTags(Site::variaveis());
    }
}
