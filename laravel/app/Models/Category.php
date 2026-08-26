<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Categoria de post. É só um jeito de separar os posts — um post de
 * "Episódios" é um post como qualquer outro (doc 02).
 *
 * As abas do site são exatamente estas linhas, na ordem de `position`.
 */
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'position'];

    protected function casts(): array
    {
        return ['position' => 'integer'];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
