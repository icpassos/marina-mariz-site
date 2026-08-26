<?php

namespace App\Filament\Resources\Media\Actions;

use App\Enums\Icone;
use App\Filament\Resources\Media\Schemas\MediaForm;
use App\Models\Media;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;

/**
 * Ações que a tabela e a tela de edição da Biblioteca compartilham (doc 05).
 *
 * Substituir é ação daqui, não da tela do item: como todo item guarda o ID
 * deste registro, trocar o arquivo aqui troca em todos de uma vez.
 */
class AcoesDeMidia
{
    public static function substituir(): Action
    {
        return Action::make('substituir')
            ->label('Substituir arquivo')
            ->icon(Icone::ArrowsClockwise)
            // Administrador e Editor editam a mídia do conteúdo (doc 05).
            ->authorize(fn (Media $record): bool => auth()->user()?->can('update', $record) ?? false)
            ->modalHeading('Substituir arquivo')
            ->modalDescription(fn (Media $record): string => self::ondeEstaEmUso($record))
            ->modalSubmitActionLabel('Substituir')
            ->schema([
                MediaForm::campoDeArquivo()
                    ->label('Arquivo novo')
                    ->required()
                    // Quem grava é o model, junto com os metadados relidos.
                    ->storeFiles(false)
                    ->helperText('O registro, o nome e o alt continuam os mesmos. Muda só o arquivo servido — e o link assinado antigo deixa de valer.'),
            ])
            ->action(function (Media $record, array $data): void {
                $record->substituirArquivo($data['path']);

                Notification::make()
                    ->success()
                    ->title('Arquivo substituído')
                    ->body('Quem usa este arquivo já está mostrando o novo.')
                    ->send();
            });
    }

    /**
     * Excluir arquivo em uso é bloqueado pelas chaves estrangeiras. A tela
     * chega antes e diz onde ele está, em vez de deixar estourar erro de SQL.
     */
    public static function excluir(): DeleteAction
    {
        return DeleteAction::make()
            ->modalDescription(fn (Media $record): string => self::emUso($record)
                ?? 'O arquivo sai da biblioteca e some do disco. Não dá para desfazer.')
            ->before(function (Media $record, DeleteAction $action): void {
                $aviso = self::emUso($record);

                if ($aviso === null) {
                    return;
                }

                Notification::make()->danger()->title('Arquivo em uso')->body($aviso)->send();

                $action->halt();
            });
    }

    private static function emUso(Media $media): ?string
    {
        $usos = $media->usos();

        return $usos === []
            ? null
            : 'Não dá para excluir: '.self::contagem($usos).' — '.implode('; ', $usos)
                .'. Troque o arquivo por lá, ou substitua este aqui.';
    }

    private static function ondeEstaEmUso(Media $media): string
    {
        $usos = $media->usos();

        return $usos === []
            ? 'Este arquivo ainda não está sendo usado em lugar nenhum.'
            : 'Este arquivo está em '.self::contagem($usos).', e a troca vale para todos de uma vez: '
                .implode('; ', $usos).'.';
    }

    /** @param  array<int, string>  $usos */
    private static function contagem(array $usos): string
    {
        return count($usos) === 1 ? '1 lugar' : count($usos).' lugares';
    }
}
