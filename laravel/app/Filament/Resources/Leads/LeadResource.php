<?php

namespace App\Filament\Resources\Leads;

use App\Enums\Icone;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Leads\Pages\ViewLead;
use App\Filament\Resources\Leads\Schemas\LeadInfolist;
use App\Filament\Resources\Leads\Tables\LeadsTable;
use App\Models\Lead;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = Icone::IdentificationBadge;

    protected static string|UnitEnum|null $navigationGroup = 'Pessoas';

    protected static ?string $navigationLabel = 'Leads';

    protected static ?string $modelLabel = 'lead';

    protected static ?string $pluralModelLabel = 'leads';

    protected static ?string $recordTitleAttribute = 'nome';

    /** Ver MensagemResource::escopoVisivel — mesmo motivo (doc 08). */
    public static function escopoVisivel(Builder $query): Builder
    {
        return $query->whereHas('downloads');
    }

    public static function getEloquentQuery(): Builder
    {
        return static::escopoVisivel(parent::getEloquentQuery())->withCount('downloads');
    }

    public static function infolist(Schema $schema): Schema
    {
        return LeadInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeads::route('/'),
            'view' => ViewLead::route('/{record}'),
        ];
    }
}
