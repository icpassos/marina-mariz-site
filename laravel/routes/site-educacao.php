<?php

use App\Http\Controllers\EducacaoSiteController;
use Illuminate\Support\Facades\Route;

/*
 * Paginas publicas de Educacao. Arquivo proprio para o modulo nao disputar
 * `routes/web.php` com o resto do site. Cada endereco e o `canonical` que
 * ja estava no HTML estatico.
 */

Route::get('/educacao', [EducacaoSiteController::class, 'hub'])->name('site.educacao');

foreach (array_keys(EducacaoSiteController::SECOES) as $secao) {
    Route::get("/educacao/{$secao}", [EducacaoSiteController::class, 'lista'])
        ->defaults('secao', $secao)
        ->name("site.educacao.{$secao}");
}
