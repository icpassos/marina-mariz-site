<?php

namespace App\Filament\Resources\Links\Tables;

use App\Enums\ContentStatus;
use App\Filament\Actions\VerNoSite;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LinksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('imagem.path')
                    ->label('')
                    ->disk('local')
                    ->visibility('private')
                    ->height(40),

                TextColumn::make('title')
                    ->label('Título')
                    ->description(fn ($record): ?string => $record->description)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('url')
                    ->label('Endereço')
                    ->searchable()
                    ->limit(48)
                    ->url(fn ($record): string => $record->url)
                    ->openUrlInNewTab()
                    ->color('primary'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // A ordem da tabela e a ordem da pagina: arrastar aqui muda /links.
            ->reorderable('position')
            ->defaultSort('position')
            ->paginated([25, 50])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ContentStatus::class),

                TrashedFilter::make()->label('Lixeira'),
            ])
            ->recordActions([
                EditAction::make(),
                VerNoSite::link(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Nenhum link ainda')
            ->emptyStateDescription('Clique em "Novo link" para começar.');
    }
}
