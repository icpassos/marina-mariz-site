<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Link;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Link> */
class LinkFactory extends Factory
{
    protected $model = Link::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'url' => 'https://exemplo.com/'.$this->faker->slug(),
            'description' => null,
            'status' => ContentStatus::Publicado,
            'position' => 0,
        ];
    }

    public function rascunho(): static
    {
        return $this->state(['status' => ContentStatus::Rascunho]);
    }
}
