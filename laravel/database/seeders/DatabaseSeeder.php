<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * O que todo ambiente precisa ter no banco antes de alguem usar o painel.
 *
 * Nada aqui cria usuario: em producao a conta nasce por
 * `php artisan make:filament-user` e o papel de administrador e marcado a
 * mao, uma vez (doc 07). Um usuario de exemplo semeado viraria porta de
 * entrada com senha conhecida.
 *
 * Os dois seeders sao idempotentes: rodar de novo nao duplica categoria
 * nem sobrescreve texto ja editado no painel.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // As quatro categorias com que o blog nasce.
        $this->call(BlogCategorySeeder::class);

        // Texto-modelo dos dois documentos legais, como rascunho.
        $this->call(TextosLegaisSeeder::class);

        // Botoes com que a pagina /links nasce.
        $this->call(LinksPadraoSeeder::class);
    }
}
