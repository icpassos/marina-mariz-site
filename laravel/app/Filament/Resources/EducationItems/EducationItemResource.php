<?php

namespace App\Filament\Resources\EducationItems;

use App\Enums\EducationType;
use App\Enums\Icone;
use App\Filament\Resources\EducationItems\Pages\CreateEducationItem;
use App\Filament\Resources\EducationItems\Pages\EditEducationItem;
use App\Filament\Resources\EducationItems\Pages\ListEducationItems;
use App\Filament\Resources\EducationItems\Schemas\EducationItemForm;
use App\Filament\Resources\EducationItems\Tables\EducationItemsTable;
use App\Models\EducationItem;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

/**
 * Um Resource so para os 6 tipos do catalogo. As seis entradas do menu
 * abrem esta mesma tela ja filtrada por tipo (doc 01).
 */
class EducationItemResource extends Resource
{
    protected static ?string $model = EducationItem::class;

    protected static string|BackedEnum|null $navigationIcon = Icone::GraduationCap;

    protected static string|UnitEnum|null $navigationGroup = 'Conteúdo';

    protected static ?string $modelLabel = 'item';

    protected static ?string $pluralModelLabel = 'itens';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return EducationItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EducationItemsTable::configure($table);
    }

    /** Sem isto o filtro de lixeira nao enxerga o que foi excluido. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    /**
     * Uma entrada por tipo, todas para o mesmo `index` com `?tipo=` fixo.
     * A Marina ve 6 itens no menu; o painel tem um CRUD so.
     *
     * @return array<NavigationItem>
     */
    public static function getNavigationItems(): array
    {
        $rota = static::getRouteBaseName().'.*';

        return collect(EducationType::cases())
            ->map(fn (EducationType $tipo, int $ordem): NavigationItem => NavigationItem::make($tipo->plural())
                ->key(static::class.':'.$tipo->value)
                ->group(static::getNavigationGroup())
                ->icon($tipo->icone())
                ->sort($ordem)
                ->isActiveWhen(fn (): bool => request()->routeIs($rota)
                    && request()->query('tipo') === $tipo->value)
                ->url(static::getUrl('index', ['tipo' => $tipo->value])))
            ->all();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEducationItems::route('/'),
            'create' => CreateEducationItem::route('/create'),
            'edit' => EditEducationItem::route('/{record}/edit'),
        ];
    }
}
