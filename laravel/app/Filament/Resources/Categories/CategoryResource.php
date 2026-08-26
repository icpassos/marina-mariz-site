<?php

namespace App\Filament\Resources\Categories;

use App\Enums\Icone;
use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Models\Category;
use App\Models\Post;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * Categorias do blog. Tela só do Administrador — o Editor apenas escolhe
 * categorias existentes ao editar um post (doc 02).
 */
class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Icone::Tag;

    protected static string|UnitEnum|null $navigationGroup = 'Conteúdo';

    protected static ?string $navigationLabel = 'Categorias do blog';

    protected static ?string $modelLabel = 'categoria';

    protected static ?string $pluralModelLabel = 'categorias';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nome')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?Category $record): void {
                    if (blank($record) && blank($get('slug'))) {
                        $set('slug', Str::slug((string) $state));
                    }
                })
                ->helperText('Muda no site na hora, inclusive nos posts já publicados.'),

            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(255)
                ->rules(['alpha_dash'])
                ->unique(ignoreRecord: true)
                ->validationMessages(['unique' => 'Já existe uma categoria com este slug.'])
                ->helperText('Usado em ?categoria=slug. Trocar muda o endereço e o anterior deixa de funcionar; não há redirect.'),

            TextInput::make('position')
                ->label('Ordem')
                ->numeric()
                ->default(0)
                ->helperText('Define a ordem das abas no site, do menor para o maior.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug')->searchable(),
                TextColumn::make('posts_count')
                    ->label('Posts')
                    ->counts('posts')
                    ->sortable(),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            // Clicar na linha abre a edicao, como no resto do painel: sem
            // isto a linha inteira nao responde e so o botao do fim funciona.
            ->recordAction(EditAction::class)
            ->headerActions([
                CreateAction::make()->label('Nova categoria'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading('Excluir categoria')
                    // Os posts não são excluídos junto: a chave estrangeira
                    // é nullOnDelete e eles passam para "Sem categoria".
                    ->modalDescription(fn (Category $record): string => match ($quantos = Post::where('category_id', $record->getKey())->count()) {
                        0 => 'Nenhum post usa esta categoria.',
                        1 => '1 post fica sem categoria. Ele não é excluído.',
                        default => "{$quantos} posts ficam sem categoria. Eles não são excluídos.",
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
        ];
    }
}
