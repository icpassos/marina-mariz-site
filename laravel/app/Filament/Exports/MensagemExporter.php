<?php

namespace App\Filament\Exports;

use App\Models\Mensagem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;

/**
 * Colunas fixas de Mensagens. Sem IP pseudonimizado, sem anotacao interna e
 * sem dado tecnico (doc 08).
 */
class MensagemExporter extends ExportadorPessoas
{
    protected static ?string $model = Mensagem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('created_at')->label('Data e hora'),
            ExportColumn::make('origem')->label('Origem'),
            ExportColumn::make('nome')->label('Nome'),
            ExportColumn::make('email')->label('E-mail'),
            ExportColumn::make('whatsapp')->label('WhatsApp'),
            ExportColumn::make('texto')->label('Mensagem'),
            ExportColumn::make('profissao')->label('Profissão'),
            ExportColumn::make('registro_profissional')->label('Registro profissional'),
            ExportColumn::make('instituicao')->label('Instituição'),
            ExportColumn::make('modalidade')->label('Modalidade'),
            ExportColumn::make('status')->label('Status'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return $export->successful_rows.' mensagem(ns) exportada(s).';
    }
}
