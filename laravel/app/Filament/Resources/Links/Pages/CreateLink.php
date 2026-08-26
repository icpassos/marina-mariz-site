<?php

namespace App\Filament\Resources\Links\Pages;

use App\Filament\Concerns\PublicaOuSalvaRascunho;
use App\Filament\Resources\Links\LinkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLink extends CreateRecord
{
    use PublicaOuSalvaRascunho;

    protected static string $resource = LinkResource::class;
}
