<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Enums\Icone;
use App\Models\Lead;
use App\Models\LeadDownload;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Primeiro contato')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->description(fn (Lead $record): string => $record->email),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('telefone')
                    ->label('Telefone')
                    ->placeholder('—'),

                TextColumn::make('downloads_count')
                    ->label('Materiais')
                    ->badge()
                    ->sortable(),

                IconColumn::make('eh_cliente')
                    ->label('Cliente')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('origem')
                    ->label('Material')
                    ->options(fn (): array => LeadDownload::query()
                        ->distinct()
                        ->orderBy('origem')
                        ->pluck('origem', 'origem')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('downloads', fn (Builder $q) => $q->where('origem', $data['value']))
                        : $query),

                TernaryFilter::make('eh_cliente')->label('Cliente'),

                Filter::make('periodo')
                    ->label('Período')
                    ->schema([
                        DatePicker::make('de')->label('De'),
                        DatePicker::make('ate')->label('Até'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['de'] ?? null, fn (Builder $q, $de) => $q->whereDate('created_at', '>=', $de))
                        ->when($data['ate'] ?? null, fn (Builder $q, $ate) => $q->whereDate('created_at', '<=', $ate))),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('marcarCliente')
                    ->label(fn (Lead $record): string => $record->eh_cliente ? 'Desmarcar cliente' : 'Marcar como cliente')
                    ->icon(Icone::SealCheck)
                    ->authorize(fn (Lead $record): bool => auth()->user()?->can('update', $record) ?? false)
                    ->action(fn (Lead $record) => $record->update(['eh_cliente' => ! $record->eh_cliente])),

                DeleteAction::make()
                    ->label('Excluir do painel')
                    ->modalDescription('Remove apenas o registro do painel. Não apaga o e-mail já entregue, os backups semanais nem os backups da hospedagem.'),
            ]);
    }
}
