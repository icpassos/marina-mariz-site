<?php

namespace App\Filament\Resources\Mensagens\Pages;

use App\Filament\Exports\MensagemExporter;
use App\Filament\Resources\Mensagens\MensagemResource;
use App\Models\Mensagem;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMensagens extends ListRecords
{
    protected static string $resource = MensagemResource::class;

    /** De onde vem e para onde vai esta seção — a Marina não acompanhou a construção. */
    protected ?string $subheading = 'Quem escreveu pelo formulário de /contato ou pelo de /educacao/formacao-profissional. Cada mensagem também chegou por e-mail em contato@dramarinamariz.com.br.';

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('Exportar CSV')
                ->exporter(MensagemExporter::class)
                ->fileDisk('local')
                // O Filament nao checa a Policy registro por registro na
                // exportacao: o recorte vem daqui, alem da Policy que
                // autoriza iniciar e baixar (doc 08).
                ->modifyQueryUsing(fn (Builder $query): Builder => MensagemResource::escopoVisivel($query))
                ->authorize(fn (): bool => auth()->user()?->can('export', Mensagem::class) ?? false),
        ];
    }
}
