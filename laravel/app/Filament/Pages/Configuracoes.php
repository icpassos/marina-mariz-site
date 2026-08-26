<?php

namespace App\Filament\Pages;

use App\Enums\Icone;
use App\Filament\Schemas\EnvioDeMidia;
use App\Models\Configuracao;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * Configuracoes (doc 06): uma tela so, dois blocos, singleton, somente
 * Administrador. Os IDs de Analytics sao dados configuraveis aqui, nunca
 * variaveis de build — trocar um ID nao pode exigir deploy.
 */
class Configuracoes extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Icone::Gear;

    protected static string|UnitEnum|null $navigationGroup = 'Sistema';

    protected static ?string $navigationLabel = 'Configurações';

    protected static ?string $title = 'Configurações';

    protected static ?string $slug = 'configuracoes';

    protected string $view = 'filament-panels::pages.page';

    /** De onde vem e para onde vai esta seção — a Marina não acompanhou a construção. */
    protected ?string $subheading = 'SEO padrão e códigos de Analytics de todas as páginas do site: valem quando o item não tem SEO próprio. Campo em branco significa script não carregado.';

    /** @var array<string, mixed> */
    public array $data = [];

    /** Pagina personalizada nao ganha Policy: a regra fica aqui e em `save`. */
    public static function canAccess(): bool
    {
        return auth()->user()?->isAdministrator() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(Configuracao::instancia()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        // Mesmo wireframe do resto do painel: o bloco que se escreve na
        // coluna larga da esquerda, o bloco de codigos na faixa estreita da
        // direita. SEO fica a esquerda porque tem texto longo e envio de
        // imagem; Analytics sao tres IDs curtos e cabem na faixa.
        return $schema
            ->statePath('data')
            ->columns(2)
            ->extraAttributes(['class' => 'fi-wp-edicao'])
            ->components([
                Section::make('SEO')
                    ->description('Usado quando a página ou o item não tem SEO próprio.')
                    ->schema([
                        TextInput::make('seo_title')
                            ->label('Título padrão do site')
                            ->required()
                            ->maxLength(60)
                            ->helperText('Até 60 caracteres. Acima disso o Google corta.'),

                        Textarea::make('seo_description')
                            ->label('Descrição padrão do site')
                            ->required()
                            ->rows(2)
                            ->maxLength(160)
                            ->helperText('Até 160 caracteres.'),

                        // Envio direto, igual ao resto do painel.
                        EnvioDeMidia::imagem('seo_image_id', 'Imagem padrão de compartilhamento')
                            ->helperText('Aparece quando um link do site é compartilhado no WhatsApp ou nas redes. Entra na Biblioteca de Mídia.'),
                    ]),

                Section::make('Analytics')
                    ->description('Campo vazio: o script não é carregado e nenhuma requisição de rastreamento sai do site. O ID sozinho não liga o rastreamento — o script só entra na página com o consentimento da visitante.')
                    ->schema([
                        TextInput::make('google_analytics_id')
                            ->label('Google Analytics (ID)')
                            ->maxLength(255)
                            ->rule($this->alternativaA('google_tag_manager_id', 'Google Tag Manager'))
                            ->helperText('Ex.: G-XXXXXXXXXX'),

                        TextInput::make('google_tag_manager_id')
                            ->label('Google Tag Manager (ID)')
                            ->maxLength(255)
                            ->rule($this->alternativaA('google_analytics_id', 'Google Analytics'))
                            ->helperText('Ex.: GTM-XXXXXXX. Com GTM ativo, o container precisa respeitar consentimento por categoria.'),

                        TextInput::make('meta_pixel_id')
                            ->label('Meta Pixel (ID)')
                            ->maxLength(255),
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
        abort_unless(static::canAccess(), 403);

        // O `saved` do model derruba o cache do site; nada aqui precisa
        // saber quais chaves sao.
        Configuracao::instancia()->update($this->form->getState());

        Notification::make()
            ->title('Configurações salvas')
            ->success()
            ->send();
    }

    /**
     * GA e GTM sao alternativas (doc 06): o painel impede os dois ativos
     * ao mesmo tempo, e recusa no servidor, nao so escondendo o campo.
     */
    private function alternativaA(string $outroCampo, string $outroNome): Closure
    {
        return static fn (Get $get): Closure => static function (string $attribute, mixed $value, Closure $fail) use ($get, $outroCampo, $outroNome): void {
            if (filled($value) && filled($get($outroCampo))) {
                $fail("Use Google Analytics ou Google Tag Manager, não os dois. Limpe o campo do {$outroNome}.");
            }
        };
    }
}
