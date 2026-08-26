<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Carrega as rotas das paginas de Educacao de um arquivo proprio, no mesmo
 * padrao do PessoasServiceProvider.
 */
class EducacaoSiteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('web')->group(base_path('routes/site-educacao.php'));
    }
}
