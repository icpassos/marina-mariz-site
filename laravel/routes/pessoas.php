<?php

use App\Http\Controllers\FormulariosController;
use Illuminate\Support\Facades\Route;

/*
 * Entradas de dado do site (doc 04). Rotas do modulo Pessoas ficam neste
 * arquivo proprio; `routes/web.php` continua sendo do site publico.
 */

// Limite por IP contra robo, alem do honeypot no Form Request.
Route::middleware('throttle:5,60')->prefix('formularios')->name('formularios.')->group(function () {
    Route::post('contato', [FormulariosController::class, 'contato'])->name('contato');
    Route::post('formacao-profissional', [FormulariosController::class, 'formacaoProfissional'])->name('formacao');
    Route::post('material', [FormulariosController::class, 'material'])->name('material');
    Route::post('newsletter', [FormulariosController::class, 'newsletter'])->name('newsletter');
});

// Descadastro de um clique: sem login e sem pedir nada alem do clique,
// como exige a LGPD (doc 04). Token de uso unico na propria URL.
Route::get('descadastrar/{token}', [FormulariosController::class, 'descadastrar'])
    ->name('newsletter.descadastrar');
