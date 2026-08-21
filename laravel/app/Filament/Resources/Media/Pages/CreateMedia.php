<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Media;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uuid'] = (string) Str::uuid();
        $data['uploaded_by'] = auth()->id();
        $data['name'] = filled($data['name'] ?? null)
            ? $data['name']
            : pathinfo($data['original_name'] ?? $data['path'], PATHINFO_FILENAME);

        return [...$data, ...Media::metadadosDe($data['path'])];
    }
}
