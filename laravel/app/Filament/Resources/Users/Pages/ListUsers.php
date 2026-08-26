<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    /** De onde vem e para onde vai esta seção — a Marina não acompanhou a construção. */
    protected ?string $subheading = 'Quem entra no painel em /paineladm, com e-mail, senha e código do aplicativo. Não aparece em lugar nenhum do site.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
