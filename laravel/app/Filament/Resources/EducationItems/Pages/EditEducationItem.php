<?php

namespace App\Filament\Resources\EducationItems\Pages;

use App\Filament\Actions\VerNoSite;
use App\Filament\Concerns\PublicaOuSalvaRascunho;
use App\Filament\Resources\EducationItems\EducationItemResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditEducationItem extends EditRecord
{
    use PublicaOuSalvaRascunho;

    protected static string $resource = EducationItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            VerNoSite::itemDeEducacao(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return EducationItemResource::getUrl('index', array_filter([
            'tipo' => $this->record?->type?->value,
        ]));
    }
}
