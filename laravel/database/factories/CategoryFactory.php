<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $nome = $this->faker->unique()->words(2, true);

        return [
            'name' => Str::ucfirst($nome),
            'slug' => Str::slug($nome),
            'position' => 0,
        ];
    }
}
