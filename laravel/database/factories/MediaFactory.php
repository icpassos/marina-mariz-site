<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    protected $model = \App\Models\Media::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'path' => 'midia/'.Str::uuid().'.webp',
            'original_name' => 'foto.webp',
            'name' => 'Foto',
            'mime_type' => 'image/webp',
            'size' => 12345,
            'width' => 1200,
            'height' => 800,
            'alt' => 'Uma foto',
            'is_decorative' => false,
        ];
    }
}
