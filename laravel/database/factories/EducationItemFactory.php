<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Enums\EducationType;
use App\Models\EducationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EducationItem>
 */
class EducationItemFactory extends Factory
{
    protected $model = EducationItem::class;

    public function definition(): array
    {
        return [
            'type' => EducationType::Livro,
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'status' => ContentStatus::Rascunho,
        ];
    }
}
