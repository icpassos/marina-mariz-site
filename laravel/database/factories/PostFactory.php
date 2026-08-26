<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Enums\PostCta;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $titulo = Str::ucfirst($this->faker->unique()->words(4, true));

        return [
            'title' => $titulo,
            'slug' => Str::slug($titulo),
            'description' => $this->faker->paragraph(),
            'content' => self::conteudo($this->faker->paragraph()),
            'author' => Post::AUTOR_PADRAO,
            'published_at' => now()->subDay(),
            'closing_cta' => PostCta::Newsletter,
            'status' => ContentStatus::Rascunho,
        ];
    }

    public function publicado(): static
    {
        return $this->state(['status' => ContentStatus::Publicado, 'published_at' => now()->subDay()]);
    }

    public function agendado(): static
    {
        return $this->state(['status' => ContentStatus::Publicado, 'published_at' => now()->addWeek()]);
    }

    public function arquivado(): static
    {
        return $this->state(['status' => ContentStatus::Arquivado]);
    }

    /** JSON do TipTap com um parágrafo — o formato que o RichEditor grava. */
    public static function conteudo(string $texto): array
    {
        return [
            'type' => 'doc',
            'content' => [[
                'type' => 'paragraph',
                'content' => [['type' => 'text', 'text' => $texto]],
            ]],
        ];
    }
}
