<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Exports\LeadExporter;
use App\Filament\Resources\Leads\LeadResource;
use App\Models\Lead;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    /** De onde vem e para onde vai esta seção — a Marina não acompanhou a construção. */
    protected ?string $subheading = 'Quem baixou material em /educacao/materiais-gratuitos ou e-book gratuito em /educacao/ebooks. O mesmo e-mail é um lead só, com o histórico do que baixou.';

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()
                ->label('Exportar CSV')
                ->exporter(LeadExporter::class)
                ->fileDisk('local')
                ->modifyQueryUsing(fn (Builder $query): Builder => LeadResource::escopoVisivel($query))
                ->authorize(fn (): bool => auth()->user()?->can('export', Lead::class) ?? false),
        ];
    }
}
