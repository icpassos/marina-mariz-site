<?php

namespace App\Filament\Resources\Links\Pages;

use App\Filament\Actions\VerNoSite;
use App\Filament\Concerns\PublicaOuSalvaRascunho;
use App\Filament\Resources\Links\LinkResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditLink extends EditRecord
{
    use PublicaOuSalvaRascunho;

    protected static string $resource = LinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            VerNoSite::link(),
            DeleteAction::make()->label('Enviar à lixeira'),
            RestoreAction::make()->label('Restaurar'),
            ForceDeleteAction::make()->label('Excluir definitivamente'),
        ];
    }
}
