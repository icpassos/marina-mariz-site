<?php

namespace App\Filament\Resources\Mensagens\Tables;

use App\Enums\Icone;
use App\Enums\OrigemMensagem;
use App\Enums\StatusMensagem;
use App\Models\Mensagem;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MensagensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Recebida em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->description(fn (Mensagem $record): string => $record->email),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                    ->options(StatusMensagem::class),

                SelectFilter::make('origem')
                    ->label('Origem')
                    ->options(OrigemMensagem::class),

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

                // Responder abre o cliente de e-mail; o painel nao envia.
                Action::make('responder')
                    ->label('Responder')
                    ->icon(Icone::ArrowUUpLeft)
                    ->url(fn (Mensagem $record): string => 'mailto:'.$record->email)
                    ->openUrlInNewTab()
                    ->authorize(fn (Mensagem $record): bool => auth()->user()?->can('update', $record) ?? false)
                    ->after(fn (Mensagem $record) => $record->update(['status' => StatusMensagem::Respondida])),

                Action::make('marcarLida')
                    ->label('Marcar como lida')
                    ->icon(Icone::EnvelopeOpen)
                    ->visible(fn (Mensagem $record): bool => $record->status === StatusMensagem::Nova)
                    ->authorize(fn (Mensagem $record): bool => auth()->user()?->can('update', $record) ?? false)
                    ->action(fn (Mensagem $record) => $record->update(['status' => StatusMensagem::Lida])),

                Action::make('arquivar')
                    ->label('Arquivar')
                    ->icon(Icone::Archive)
                    ->requiresConfirmation()
                    ->visible(fn (Mensagem $record): bool => $record->status !== StatusMensagem::Arquivada)
                    ->authorize(fn (Mensagem $record): bool => auth()->user()?->can('update', $record) ?? false)
                    ->action(fn (Mensagem $record) => $record->update(['status' => StatusMensagem::Arquivada])),

                Action::make('anotar')
                    ->label('Anotação interna')
                    ->icon(Icone::NotePencil)
                    ->authorize(fn (Mensagem $record): bool => auth()->user()?->can('update', $record) ?? false)
                    ->schema([
                        Textarea::make('anotacao')
                            ->label('Anotação interna')
                            ->rows(4)
                            ->maxLength(2000)
                            ->helperText('Fica só no painel. Não vai para a exportação nem para o e-mail.'),
                    ])
                    ->fillForm(fn (Mensagem $record): array => ['anotacao' => $record->anotacao])
                    ->action(fn (Mensagem $record, array $data) => $record->update(['anotacao' => $data['anotacao']])),

                // Excluir do painel remove so a linha operacional (doc 04).
                DeleteAction::make()
                    ->label('Excluir do painel')
                    ->modalDescription('Remove apenas o registro do painel. Não apaga o e-mail já entregue, os backups semanais nem os backups da hospedagem.'),
            ]);
    }
}
