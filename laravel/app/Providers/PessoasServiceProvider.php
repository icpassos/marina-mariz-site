<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Carrega as rotas publicas do modulo Pessoas de um arquivo proprio, para o
 * modulo nao disputar `routes/web.php` com o site publico.
 */
class PessoasServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('web')->group(base_path('routes/pessoas.php'));
    }
}
