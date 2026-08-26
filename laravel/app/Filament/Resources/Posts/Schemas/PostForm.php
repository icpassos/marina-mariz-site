<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\ContentStatus;
use App\Enums\PostCta;
use App\Filament\Schemas\AcoesDoFormulario;
use App\Filament\Schemas\CampoDeLink;
use App\Filament\Schemas\CamposDeSeo;
use App\Filament\Schemas\EnvioDeMidia;
use App\Models\Post;
use App\Support\Publicacao;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PostForm
{
    /**
     * Duas colunas, como a tela de edicao do WordPress: o que se escreve fica
     * na coluna larga da esquerda; publicacao, categoria, imagem e SEO ficam
     * na faixa estreita da direita. A largura da faixa (280px) e o empilhamento
     * em tela estreita vem do tema, em `.fi-wp-edicao`.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->extraAttributes(['class' => 'fi-wp-edicao'])
            ->components([
                Group::make(self::conteudo()),
                Group::make(self::apoio()),
            ]);
    }

    /** Coluna principal: o texto do post. */
    private static function conteudo(): array
    {
        return [
            Section::make('Post')
                ->description('Campos marcados com * são obrigatórios para publicar. Um rascunho precisa só do título.')
                ->schema([
                    TextInput::make('title')
                        ->label('Título *')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?Post $record): void {
                            // Sugere, não impõe: slug já escrito à mão fica como está.
                            if (blank($record) && blank($get('slug'))) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),

                    TextInput::make('slug')
                        ->label('Slug *')
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->rules(['alpha_dash'])
                        ->unique(ignoreRecord: true)
                        ->validationMessages(['unique' => 'Já existe um post com este slug.'])
                        ->helperText(fn (Get $get, ?Post $record): string => self::avisoDoSlug($get, $record)),

                    Textarea::make('description')
                        ->label('Descrição *')
                        ->rows(5)
                        ->maxLength(300)
                        ->required(Publicacao::exigido())
                        ->helperText('2 a 3 linhas. Aparece no card do blog e no Google.'),

                    RichEditor::make('content')
                        ->label('Conteúdo *')
                        ->json()
                        // Barra de edicao no espirito da do WordPress: os
                        // grupos aparecem na ordem em que se usa um texto —
                        // primeiro o nivel do titulo, depois a enfase, o
                        // alinhamento, as listas e por fim o que se insere.
                        // Fora de proposito: `h1` (o titulo do post ja e o h1
                        // da pagina, dois quebram a hierarquia e o SEO) e cor
                        // de texto (a paleta do site e fechada).
                        ->toolbarButtons([
                            ['paragraph', 'h2', 'h3'],
                            ['bold', 'italic', 'underline', 'strike', 'link'],
                            ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify'],
                            ['bulletList', 'orderedList', 'blockquote', 'horizontalRule'],
                            ['table', 'attachFiles'],
                            ['clearFormatting', 'undo', 'redo'],
                        ])
                        ->required(Publicacao::exigido())
                        ->helperText('Imagem colada aqui entra na Biblioteca de Mídia.'),
                ]),

            Section::make('CTA de fechamento')
                ->description('Fecha o post. São quatro, fixos. Newsletter e consulta não pedem nada: texto e botão já vêm prontos.')
                ->schema([
                    Select::make('closing_cta')
                        ->label('CTA de fechamento *')
                        ->options(PostCta::class)
                        ->live()
                        ->required(Publicacao::exigido()),

                    Textarea::make('material_description')
                        ->label('Descrição do material *')
                        ->rows(5)
                        ->maxLength(300)
                        ->visible(self::quandoOCtaFor(PostCta::Material))
                        ->required(Publicacao::exigidoQuando(self::quandoOCtaFor(PostCta::Material)))
                        ->helperText('2 a 3 linhas. Substitui a descrição fixa do CTA neste post.'),

                    EnvioDeMidia::arquivo('material_media_id', 'Arquivo para download *', ['pdf', 'epub', 'csv', 'xlsx', 'mp4', 'webm', 'mp3', 'm4a', 'wav'])
                        ->visible(self::quandoOCtaFor(PostCta::Material))
                        ->required(Publicacao::exigidoQuando(self::quandoOCtaFor(PostCta::Material)))
                        ->helperText('É o arquivo deste post, sem vínculo com o catálogo de Educação — o botão baixa direto, sem pedir nome e e-mail. Entra na Biblioteca de Mídia.'),

                    TextInput::make('episode_url')
                        ->label('Link do episódio *')
                        ->url()
                        ->maxLength(255)
                        ->rules(['starts_with:https://'])
                        ->validationMessages(['starts_with' => 'O link precisa começar com https://'])
                        ->extraInputAttributes(CampoDeLink::comecaEmHttps())
                        ->visible(self::quandoOCtaFor(PostCta::Podcast))
                        ->required(Publicacao::exigidoQuando(self::quandoOCtaFor(PostCta::Podcast)))
                        ->helperText('Endereço do vídeo no YouTube do Sem Neura Podcast. Abre em nova aba.'),
                ]),

            Section::make('Posts relacionados')
                ->schema([
                    Select::make('relacionados')
                        ->label('Posts relacionados')
                        ->relationship(
                            'relacionados',
                            'title',
                            fn (Builder $query, ?Post $record) => $query->when($record, fn (Builder $q) => $q->whereKeyNot($record->getKey())),
                        )
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->maxItems(Post::MAXIMO_DE_RELACIONADOS)
                        ->rules(['array', 'max:'.Post::MAXIMO_DE_RELACIONADOS])
                        ->helperText('Até 4, escolhidos à mão. Relacionado arquivado ou excluído simplesmente não aparece no site.'),
                ]),
        ];
    }

    /** Coluna de apoio: decisões sobre o post, não o texto dele. */
    private static function apoio(): array
    {
        return [
            Section::make('Publicação')
                ->schema([
                    Select::make('status')
                        ->label('Status *')
                        ->options(ContentStatus::class)
                        ->default(ContentStatus::Rascunho)
                        ->selectablePlaceholder(false)
                        ->required()
                        ->live()
                        // Arquivar e desarquivar são do Administrador; o
                        // model recusa de novo no servidor.
                        ->disableOptionWhen(fn (string $value): bool => $value === ContentStatus::Arquivado->value
                            && ! auth()->user()?->isAdministrator()),

                    DateTimePicker::make('published_at')
                        ->label('Data de publicação *')
                        ->seconds(false)
                        ->required(Publicacao::exigido())
                        ->helperText('Horário UTC. Data futura com status Publicado deixa o post agendado: ele entra no site sozinho quando a data chega.'),

                    TextInput::make('author')
                        ->label('Autor')
                        ->maxLength(255)
                        ->default(Post::AUTOR_PADRAO),

                    // Caixa "Publicar" do WordPress: os mesmos botoes do
                    // rodape, ao alcance sem rolar a pagina inteira.
                    AcoesDoFormulario::noTopo(),
                ]),

            Section::make('Categoria')
                ->schema([
                    Select::make('category_id')
                        ->label('Categoria')
                        ->relationship('categoria', 'name', fn (Builder $query) => $query->orderBy('position'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Sem categoria')
                        ->helperText('Em branco o post publica assim mesmo e entra em "Sem categoria".'),
                ]),

            Section::make('Imagem destacada')
                ->schema([
                    EnvioDeMidia::imagem('image_id', 'Imagem *')
                        ->required(Publicacao::exigido())
                        ->helperText('Proporção sugerida 16:9. O navegador converte para WebP e o arquivo entra na Biblioteca de Mídia.'),
                ]),

            CamposDeSeo::secao(),
        ];
    }

    /** O Livewire manda ora o enum, ora a string — `PostCta::de` resolve os dois. */
    private static function quandoOCtaFor(PostCta $cta): \Closure
    {
        return fn (Get $get): bool => PostCta::de($get('closing_cta')) === $cta;
    }

    private static function avisoDoSlug(Get $get, ?Post $record): string
    {
        $mudou = $record !== null && $get('slug') !== $record->slug;

        return $mudou && $record->estaPublicado()
            ? '⚠ Este post está publicado. Trocar o slug muda a URL: o endereço anterior passa a responder 404 e não existe redirect.'
            : 'Sugerido pelo título, editável e único. É o endereço do post no site.';
    }
}
