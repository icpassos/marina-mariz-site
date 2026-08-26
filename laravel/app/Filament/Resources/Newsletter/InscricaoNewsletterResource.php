<?php

namespace App\Filament\Resources\Newsletter;

use App\Enums\Icone;
use App\Filament\Resources\Newsletter\Pages\ListInscricoes;
use App\Filament\Resources\Newsletter\Pages\ViewInscricao;
use App\Filament\Resources\Newsletter\Schemas\InscricaoInfolist;
use App\Filament\Resources\Newsletter\Tables\InscricoesTable;
use App\Models\InscricaoNewsletter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class InscricaoNewsletterResource extends Resource
{
    protected static ?string $model = InscricaoNewsletter::class;

    protected static string|BackedEnum|null $navigationIcon = Icone::EnvelopeSimple;

    protected static string|UnitEnum|null $navigationGroup = 'Pessoas';

    protected static ?string $navigationLabel = 'Newsletter';

    protected static ?string $modelLabel = 'inscrito';

    protected static ?string $pluralModelLabel = 'inscritos';

    protected static ?string $recordTitleAttribute = 'email';

    /** Ver MensagemResource::escopoVisivel — mesmo motivo (doc 08). */
    public static function escopoVisivel(Builder $query): Builder
    {
        return $query->whereNotNull('email');
    }

    public static function getEloquentQuery(): Builder
    {
        return static::escopoVisivel(parent::getEloquentQuery());
    }

    public static function infolist(Schema $schema): Schema
    {
        return InscricaoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InscricoesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInscricoes::route('/'),
            'view' => ViewInscricao::route('/{record}'),
        ];
    }
}
