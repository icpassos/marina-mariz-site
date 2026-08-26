<?php

namespace App\Filament\Exports;

use App\Models\Lead;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

/** Colunas fixas de Leads. Sem IP pseudonimizado (doc 08). */
class LeadExporter extends ExportadorPessoas
{
    protected static ?string $model = Lead::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('created_at')->label('Data e hora'),
            ExportColumn::make('nome')->label('Nome'),
            ExportColumn::make('email')->label('E-mail'),
            ExportColumn::make('telefone')->label('Telefone'),
            ExportColumn::make('materiais')
                ->label('Materiais baixados')
                ->state(fn (Lead $record): string => $record->downloads->pluck('origem')->implode(' | ')),
            ExportColumn::make('eh_cliente')
                ->label('Cliente')
                ->state(fn (Lead $record): string => $record->eh_cliente ? 'sim' : 'não'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return $export->successful_rows.' lead(s) exportado(s).';
    }
}
