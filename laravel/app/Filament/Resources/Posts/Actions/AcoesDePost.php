<?php

namespace App\Filament\Resources\Posts\Actions;

use App\Enums\ContentStatus;
use App\Enums\Icone;
use App\Models\Post;
use App\Support\ExportaPost;
use Filament\Actions\Action;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Ações que a tabela e a tela de edição compartilham.
 *
 * Baixar PDF e Word são dos dois papéis; arquivar e desarquivar, só do
 * Administrador — e o model confere de novo no `save()` (doc 02).
 */
class AcoesDePost
{
    public static function baixarPdf(): Action
    {
        return Action::make('baixarPdf')
            ->label('Baixar PDF')
            ->icon(Icone::DownloadSimple)
            ->action(fn (Post $record): BinaryFileResponse => ExportaPost::pdf($record));
    }

    public static function baixarWord(): Action
    {
        return Action::make('baixarWord')
            ->label('Baixar Word')
            ->icon(Icone::DownloadSimple)
            ->action(fn (Post $record): BinaryFileResponse => ExportaPost::docx($record));
    }

    public static function arquivar(): Action
    {
        return Action::make('arquivar')
            ->label('Arquivar')
            ->icon(Icone::Archive)
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Arquivar post')
            ->modalDescription('Arquivar tira o post do site e guarda tudo. Não é excluir: dá para desarquivar depois, no estado em que estava.')
            ->visible(fn (Post $record): bool => ! $record->trashed()
                && $record->status !== ContentStatus::Arquivado
                && auth()->user()?->can('arquivar', $record))
            ->action(fn (Post $record) => $record->update(['status' => ContentStatus::Arquivado]));
    }

    public static function desarquivar(): Action
    {
        return Action::make('desarquivar')
            ->label('Desarquivar')
            ->icon(Icone::ArrowUUpLeft)
            ->requiresConfirmation()
            ->modalDescription('O post volta como rascunho, para você conferir antes de publicar de novo.')
            ->visible(fn (Post $record): bool => ! $record->trashed()
                && $record->status === ContentStatus::Arquivado
                && auth()->user()?->can('arquivar', $record))
            ->action(fn (Post $record) => $record->update(['status' => ContentStatus::Rascunho]));
    }
}
