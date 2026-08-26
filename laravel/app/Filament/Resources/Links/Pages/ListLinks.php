<?php

namespace App\Filament\Resources\Links\Pages;

use App\Enums\Icone;
use App\Filament\Resources\Links\LinkResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLinks extends ListRecords
{
    protected static string $resource = LinkResource::class;

    /** De onde vem e para onde vai esta seção — a Marina não acompanhou a construção. */
    protected ?string $subheading = 'Os botões de /links, a página de "link na bio" para o Instagram. Ela não aparece no menu, no rodapé nem no Google: só chega quem clica no link do perfil. A ordem aqui é a ordem lá — arraste para mudar.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Novo link'),

            Action::make('verPagina')
                ->label('Ver a página')
                ->icon(Icone::ArrowUpRight)
                ->color('gray')
                ->url(route('site.links'))
                ->openUrlInNewTab(),
        ];
    }
}
