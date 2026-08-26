<?php

namespace App\Filament\Schemas;

use App\Models\OutboxJob;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Model;

/**
 * Pendencias, tentativas e ultimo erro da outbox (docs 04 e 08).
 *
 * So o Administrador ve. O visitante nunca soube que o aviso falhou, e o
 * Editor tambem nao precisa saber.
 */
class SecaoOutbox
{
    public static function secao(): Section
    {
        return Section::make('Entrega do e-mail de aviso')
            ->collapsed()
            ->visible(fn (): bool => auth()->user()?->isAdministrator() ?? false)
            ->schema([
                TextEntry::make('outbox')
                    ->hiddenLabel()
                    ->state(fn (Model $record): string => self::resumo($record))
                    ->html(),
            ]);
    }

    private static function resumo(Model $record): string
    {
        $itens = OutboxJob::query()
            ->where('assunto_type', $record->getMorphClass())
            ->where('assunto_id', $record->getKey())
            ->orderBy('id')
            ->get();

        if ($itens->isEmpty()) {
            return 'Nada na fila para este registro.';
        }

        return $itens
            ->map(fn (OutboxJob $item): string => e(sprintf(
                '%s · %d tentativa(s)%s',
                $item->statusLabel(),
                $item->tentativas,
                $item->ultimo_erro ? ' · último erro: '.$item->ultimo_erro : '',
            )))
            ->implode('<br>');
    }
}
