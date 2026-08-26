<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\ContentStatus;
use App\Enums\Icone;
use App\Filament\Actions\VerNoSite;
use App\Filament\Resources\Posts\Actions\AcoesDePost;
use App\Models\Category;
use App\Models\Post;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class PostsTable
{
    /** Valor do filtro de categoria que significa "sem categoria nenhuma". */
    public const SEM_CATEGORIA = 'sem';

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Post $record): string => '/blog/'.$record->slug)
                    ->wrap(),

                TextColumn::make('categoria.name')
                    ->label('Categoria')
                    ->badge()
                    ->default('Sem categoria')
                    ->color(fn (string $state): string => $state === 'Sem categoria' ? 'warning' : 'gray'),

                // "Agendado" é derivado de Publicado com data futura; não
                // existe como valor no banco (doc 02).
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->state(fn (Post $record): string => $record->rotuloDeEstado())
                    ->color(fn (string $state, Post $record): string => $state === 'Agendado' ? 'info' : $record->status->getColor()),

                TextColumn::make('published_at')
                    ->label('Publicação')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('published_at', 'desc')
            ->reorderable('position')
            ->filters([
                // Começa escondendo os arquivados (doc 02); quem quiser vê-los
                // acrescenta o estado no filtro.
                SelectFilter::make('status')
                    ->label('Status')
                    ->multiple()
                    ->options(ContentStatus::class)
                    ->default([ContentStatus::Rascunho->value, ContentStatus::Publicado->value]),

                SelectFilter::make('categoria')
                    ->label('Categoria')
                    ->options(fn (): array => [self::SEM_CATEGORIA => 'Sem categoria']
                        + Category::orderBy('position')->pluck('name', 'id')->all())
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        null, '' => $query,
                        self::SEM_CATEGORIA => $query->whereNull('category_id'),
                        default => $query->where('category_id', $data['value']),
                    }),

                TrashedFilter::make()->label('Lixeira'),
            ])
            // Sem pagina de visualizacao no painel: clicar na linha abre o
            // editor direto. "Ver no site" e o endereco publico de verdade.
            ->recordActions([
                EditAction::make(),
                VerNoSite::post(),
                ActionGroup::make([
                    AcoesDePost::baixarPdf(),
                    AcoesDePost::baixarWord(),
                    ReplicateAction::make()
                        ->label('Duplicar')
                        ->authorize(fn (): bool => auth()->user()?->isAdministrator() ?? false)
                        ->excludeAttributes(['slug', 'published_at', 'status'])
                        ->beforeReplicaSaved(function (Post $replica, Post $record): void {
                            $replica->title = $record->title.' (cópia)';
                            $replica->slug = Str::slug($replica->title).'-'.Str::lower(Str::random(5));
                            $replica->status = ContentStatus::Rascunho;
                            $replica->published_at = null;
                        }),
                    AcoesDePost::arquivar(),
                    AcoesDePost::desarquivar(),
                    DeleteAction::make()->label('Enviar à lixeira'),
                    RestoreAction::make()->label('Restaurar'),
                    ForceDeleteAction::make()->label('Excluir definitivamente'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::trocarDeStatus('arquivarEmLote', 'Arquivar', ContentStatus::Arquivado),
                    self::trocarDeStatus('desarquivarEmLote', 'Desarquivar', ContentStatus::Rascunho),
                    self::trocarDeCategoria(),
                    DeleteBulkAction::make()->label('Enviar à lixeira'),
                    RestoreBulkAction::make()->label('Restaurar'),
                    ForceDeleteBulkAction::make()->label('Excluir definitivamente'),
                ]),
            ]);
    }

    private static function trocarDeStatus(string $nome, string $rotulo, ContentStatus $destino): BulkAction
    {
        return BulkAction::make($nome)
            ->label($rotulo)
            ->icon($destino === ContentStatus::Arquivado ? Icone::Archive : Icone::ArrowUUpLeft)
            ->requiresConfirmation()
            ->authorize(fn (): bool => auth()->user()?->isAdministrator() ?? false)
            ->action(fn (Collection $records) => $records->each->update(['status' => $destino]))
            ->deselectRecordsAfterCompletion();
    }

    private static function trocarDeCategoria(): BulkAction
    {
        return BulkAction::make('trocarDeCategoria')
            ->label('Trocar de categoria')
            ->icon(Icone::Tag)
            ->authorize(fn (): bool => auth()->user()?->isAdministrator() ?? false)
            ->schema([
                Select::make('category_id')
                    ->label('Categoria')
                    ->options(fn (): array => Category::orderBy('position')->pluck('name', 'id')->all())
                    ->placeholder('Sem categoria'),
            ])
            ->action(fn (Collection $records, array $data) => $records->each->update([
                'category_id' => $data['category_id'] ?: null,
            ]))
            ->deselectRecordsAfterCompletion();
    }
}
