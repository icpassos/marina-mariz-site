<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\DownloadProtegidoController;
use App\Http\Controllers\LinksController;
use App\Http\Controllers\PreferenciasDeCookiesController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// Assinatura e validade sao conferidas pelo middleware antes de chegar
// no controller; link expirado responde 403.
Route::get('/download/{media}', DownloadProtegidoController::class)
    ->middleware('signed')
    ->name('download.protegido');

// Aviso de Cookies. Formulario comum, para a escolha valer sem JavaScript.
Route::post('/preferencias-de-cookies', PreferenciasDeCookiesController::class)
    ->name('cookies.preferencias');

// Lista de enderecos para o Google. Gerada na hora: post publicado pelo
// painel entra sozinho, sem ninguem editar arquivo.
Route::get('/sitemap.xml', SitemapController::class)->name('site.sitemap');

// ── Site publico ─────────────────────────────────────────────────────
// Cada endereco e o `canonical` que ja estava no HTML estatico.
foreach ([
    '/' => 'index',
    '/sobre' => 'sobre',
    '/especialidades' => 'especialidades',
    '/amara' => 'amara',
    '/podcast' => 'podcast',
    '/contato' => 'contato',
    '/newsletter' => 'newsletter',
] as $caminho => $pagina) {
    Route::get($caminho, [SiteController::class, 'pagina'])
        ->defaults('pagina', $pagina)
        ->name('site.'.$pagina);
}

foreach (['politica-de-privacidade', 'termos-de-uso'] as $chave) {
    Route::get('/'.$chave, [SiteController::class, 'textoLegal'])
        ->defaults('chave', $chave)
        ->name('site.'.$chave);
}

// ── Links ────────────────────────────────────────────────────────────
// Agregador de "link na bio". Fora do menu, do rodape e do sitemap de
// proposito: o caminho ate aqui e o perfil no Instagram (doc 09).
Route::get('/links', LinksController::class)->name('site.links');

// ── Blog ─────────────────────────────────────────────────────────────
// Lista, busca e post. O recorte publico e do controller; o que nao esta
// no ar cai no 404 padrao do site, sem redirect e sem 410.
Route::get('/blog', [BlogController::class, 'lista'])->name('site.blog');
Route::get('/blog/{slug}', [BlogController::class, 'post'])->name('site.blog.post');

// Permalink do WordPress antigo (`/2022/03/11/slug/`). O post existe, so
// mudou de endereco: 301 leva a visita e o Google para o lugar novo. Slug
// que nao virou post nenhum cai no 404 do site, sem redirect as cegas.
Route::get('/{ano}/{mes}/{dia}/{slug}', [BlogController::class, 'permalinkAntigo'])
    ->where(['ano' => '\d{4}', 'mes' => '\d{2}', 'dia' => '\d{2}', 'slug' => '[a-z0-9\-]+'])
    ->name('site.blog.permalink-antigo');

// Mantem a pagina 404 dentro do grupo web para ela receber os mesmos
// cabecalhos de seguranca das demais paginas publicas.
Route::fallback(fn () => response()->view('errors.404', status: 404))
    ->name('site.nao-encontrada');
