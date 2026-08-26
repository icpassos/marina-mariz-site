<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    /** De onde vem e para onde vai esta seção — a Marina não acompanhou a construção. */
    protected ?string $subheading = 'Retrato de tudo que já foi enviado pelas telas de Blog e de Educação. Substituir o arquivo aqui troca em todos os conteúdos que o usam de uma vez.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
