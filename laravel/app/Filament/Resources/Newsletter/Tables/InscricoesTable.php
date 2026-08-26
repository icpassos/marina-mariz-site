<?php

namespace App\Filament\Resources\Newsletter\Tables;

use App\Enums\StatusInscricao;
use App\Http\Requests\NewsletterRequest;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InscricoesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Inscrição')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('origem')
                    ->label('Origem')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(StatusInscricao::class),

                SelectFilter::make('origem')
                    ->label('Origem')
                    ->options(array_combine(NewsletterRequest::ORIGENS, NewsletterRequest::ORIGENS)),

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

                DeleteAction::make()
                    ->label('Excluir do painel')
                    ->modalDescription('Remove apenas o registro do painel. Não apaga o e-mail já entregue, os backups semanais nem os backups da hospedagem.'),
            ]);
    }
}
