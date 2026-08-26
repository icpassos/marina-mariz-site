<?php

namespace App\Filament\Resources\Links\Schemas;

use App\Enums\ContentStatus;
use App\Filament\Schemas\AcoesDoFormulario;
use App\Filament\Schemas\EnvioDeMidia;
use App\Models\Link;
use App\Support\Publicacao;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;

/**
 * Duas colunas, como as outras telas de edicao: o que se escreve na coluna
 * larga da esquerda, a decisao de publicar na faixa estreita da direita.
 */
class LinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->extraAttributes(['class' => 'fi-wp-edicao'])
            ->components([
                Group::make([
                    Section::make('Link')
                        ->description('Campos marcados com * são obrigatórios para publicar. Um rascunho precisa só do título.')
                        ->schema([
                            TextInput::make('title')
                                ->label('Título *')
                                ->required()
                                ->maxLength(120)
                                ->helperText('O que aparece escrito no botão. Curto funciona melhor no celular.'),

                            TextInput::make('url')
                                ->label('Endereço *')
                                ->maxLength(255)
                                ->required(Publicacao::exigido())
                                // Aceita os dois formatos que a pagina usa e
                                // recusa o resto — inclusive `javascript:`,
                                // que viraria script no clique.
                                ->rules(['regex:#^(https://\S+|/\S*)$#'])
                                ->validationMessages([
                                    'regex' => 'Use um endereço começando com https:// ou uma página deste site, como /blog.',
                                ])
                                ->helperText('Endereço de fora (https://…) abre em aba nova. Página deste site (/blog, /educacao/ebooks) abre na mesma aba.'),

                            TextInput::make('description')
                                ->label('Linha de apoio')
                                ->maxLength(160)
                                ->helperText('Opcional. Uma frase curta embaixo do título, para explicar o que a pessoa encontra do outro lado.'),

                            EnvioDeMidia::imagem('image_id', 'Miniatura')
                                ->helperText('Opcional. Aparece como um quadradinho à esquerda do título. Entra na Biblioteca de Mídia.'),
                        ]),
                ]),

                Group::make([
                    Section::make('Publicação')
                        ->schema([
                            Select::make('status')
                                ->label('Status *')
                                ->options(ContentStatus::class)
                                ->default(ContentStatus::Rascunho)
                                ->selectablePlaceholder(false)
                                ->required()
                                ->live()
                                ->disableOptionWhen(fn (string $value): bool => $value === ContentStatus::Arquivado->value
                                    && ! Gate::allows('arquivar', Link::class))
                                ->helperText('Publicado aparece em /links na hora. Rascunho e arquivado não aparecem.'),

                            AcoesDoFormulario::noTopo(),
                        ]),
                ]),
            ]);
    }
}
