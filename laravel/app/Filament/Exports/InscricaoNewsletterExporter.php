<?php

namespace App\Filament\Exports;

use App\Models\InscricaoNewsletter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

/**
 * Colunas fixas da Newsletter. Sem IP pseudonimizado (doc 08).
 *
 * O link de descadastro entra de proposito: o disparo acontece fora do
 * painel, e sem esta coluna nao ha como cumprir "link de descadastro em todo
 * e-mail de newsletter" na ferramenta da vez.
 */
class InscricaoNewsletterExporter extends ExportadorPessoas
{
    protected static ?string $model = InscricaoNewsletter::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('created_at')->label('Data e hora'),
            ExportColumn::make('nome')->label('Nome'),
            ExportColumn::make('email')->label('E-mail'),
            ExportColumn::make('origem')->label('Origem'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('link_descadastro')
                ->label('Link de descadastro')
                ->state(fn (InscricaoNewsletter $record): string => (string) $record->linkDeDescadastro()),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return $export->successful_rows.' inscrito(s) exportado(s).';
    }
}
