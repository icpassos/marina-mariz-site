<?php

namespace App\Filament\Resources\Media\Tables;

use App\Models\Media;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('')
                    ->disk('local')
                    ->visibility('private')
                    ->height(56)
                    ->defaultImageUrl(null),

                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(['name', 'original_name'])
                    ->sortable()
                    ->description(fn (Media $record): string => $record->original_name),

                TextColumn::make('dimensoes')
                    ->label('Dimensões')
                    ->state(fn (Media $record): string => $record->width
                        ? "{$record->width} × {$record->height}"
                        : '—'),

                TextColumn::make('size')
                    ->label('Peso')
                    ->formatStateUsing(fn (int $state): string => self::peso($state))
                    ->sortable(),

                TextColumn::make('alt')
                    ->label('Alt')
                    ->state(fn (Media $record): string => match (true) {
                        ! $record->isImage() => '—',
                        $record->is_decorative => 'decorativa',
                        filled($record->alt) => 'ok',
                        default => 'falta',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'falta' => 'danger',
                        'ok' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Enviado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'image' => 'Imagens',
                        'application' => 'Documentos',
                        'video' => 'Vídeos',
                        'audio' => 'Áudios',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->where('mime_type', 'like', $data['value'].'/%')
                        : $query),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    private static function peso(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024).' KB';
        }

        return round($bytes / 1024 / 1024, 1).' MB';
    }
}
