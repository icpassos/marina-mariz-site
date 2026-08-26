<?php

namespace App\Filament\Resources\EducationItems\Pages;

use App\Filament\Concerns\PublicaOuSalvaRascunho;
use App\Filament\Resources\EducationItems\EducationItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEducationItem extends CreateRecord
{
    use PublicaOuSalvaRascunho;

    protected static string $resource = EducationItemResource::class;

    /** Volta para a lista do tipo de onde a pessoa veio. */
    protected function getRedirectUrl(): string
    {
        return EducationItemResource::getUrl('index', array_filter([
            'tipo' => $this->record?->type?->value,
        ]));
    }
}
