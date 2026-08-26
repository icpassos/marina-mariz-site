<?php

namespace App\Filament\Resources\EducationItems\Tables;

use App\Enums\ContentStatus;
use App\Enums\EducationType;
use App\Filament\Actions\VerNoSite;
use App\Filament\Resources\EducationItems\Pages\ListEducationItems;
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

class EducationItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('imagem.path')
                    ->label('')
                    ->disk('local')
                    ->visibility('private')
                    ->height(48),

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),

                TextColumn::make('starts_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // Ordem por arrastar, dentro do tipo que a lista esta mostrando.
            ->reorderable('position')
            ->defaultSort('position')
            ->paginated([12, 24, 48])
            ->defaultPaginationPageOption(12)
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ContentStatus::class),

                // A entrada do menu ja fixa o tipo; o filtro so faz sentido
                // na lista sem tipo.
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(EducationType::class)
                    ->visible(fn (ListEducationItems $livewire): bool => $livewire->tipoAtual() === null),

                TrashedFilter::make()->label('Lixeira'),
            ])
            ->recordActions([
                EditAction::make(),
                VerNoSite::itemDeEducacao(),
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
            ->emptyStateHeading('Nenhum item ainda')
            ->emptyStateDescription('Clique em "Novo item" para começar.');
    }
}
