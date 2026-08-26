<?php

namespace App\Filament\Resources\Newsletter\Pages;

use App\Filament\Exports\InscricaoNewsletterExporter;
use App\Filament\Resources\Newsletter\InscricaoNewsletterResource;
use App\Models\InscricaoNewsletter;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInscricoes extends ListRecords
{
    protected static string $resource = InscricaoNewsletterResource::class;

    /** De onde vem e para onde vai esta seção — a Marina não acompanhou a construção. */
    protected ?string $subheading = 'Quem pediu para receber avisos em /blog, em /educacao/eventos ou em /educacao/livros. O envio acontece fora do painel, pelo CSV exportado; o descadastro é feito em /descadastrar.';

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('Exportar CSV')
                ->exporter(InscricaoNewsletterExporter::class)
                ->fileDisk('local')
                ->modifyQueryUsing(fn (Builder $query): Builder => InscricaoNewsletterResource::escopoVisivel($query))
                ->authorize(fn (): bool => auth()->user()?->can('export', InscricaoNewsletter::class) ?? false),
        ];
    }
}
