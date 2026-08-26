<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/** As quatro categorias com que o blog nasce (doc 02). */
class BlogCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Artigos', 'Episódios', 'Materiais', 'Relatos de Parto'] as $ordem => $nome) {
            Category::firstOrCreate(
                ['slug' => str($nome)->slug()->value()],
                ['name' => $nome, 'position' => $ordem],
            );
        }
    }
}
