<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\Actions\AcoesDeMidia;
use App\Filament\Resources\Media\MediaResource;
use Filament\Resources\Pages\EditRecord;

class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            AcoesDeMidia::substituir(),
            AcoesDeMidia::excluir(),
        ];
    }
}
