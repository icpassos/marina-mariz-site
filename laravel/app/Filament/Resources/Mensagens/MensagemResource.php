<?php

namespace App\Filament\Resources\Mensagens;

use App\Enums\Icone;
use App\Enums\StatusMensagem;
use App\Filament\Resources\Mensagens\Pages\ListMensagens;
use App\Filament\Resources\Mensagens\Pages\ViewMensagem;
use App\Filament\Resources\Mensagens\Schemas\MensagemInfolist;
use App\Filament\Resources\Mensagens\Tables\MensagensTable;
use App\Models\Mensagem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MensagemResource extends Resource
{
    protected static ?string $model = Mensagem::class;

    protected static string|BackedEnum|null $navigationIcon = Icone::ChatsCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Pessoas';

    protected static ?string $navigationLabel = 'Mensagens';

    protected static ?string $modelLabel = 'mensagem';

    protected static ?string $pluralModelLabel = 'mensagens';

    protected static ?string $recordTitleAttribute = 'nome';

    /**
     * Recorte visivel do modulo, em um lugar so. A lista e a exportacao usam
     * este mesmo metodo: o doc 08 exige que o `ExportAction` tenha query
     * explicitamente limitada, porque o Filament nao consulta a Policy
     * registro por registro durante a exportacao.
     */
    public static function escopoVisivel(Builder $query): Builder
    {
        return $query->whereNotNull('submission_id');
    }

    public static function getEloquentQuery(): Builder
    {
        return static::escopoVisivel(parent::getEloquentQuery());
    }

    public static function getNavigationBadge(): ?string
    {
        $novas = static::getEloquentQuery()->where('status', StatusMensagem::Nova)->count();

        return $novas > 0 ? (string) $novas : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return MensagemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MensagensTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMensagens::route('/'),
            'view' => ViewMensagem::route('/{record}'),
        ];
    }
}
