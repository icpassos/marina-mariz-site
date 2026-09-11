<?php

use App\Http\Middleware\CabecalhosDeSeguranca;
use App\Http\Middleware\LeConsentimentoDeCookies;
use App\Support\AvisoDeCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // A interface de preferencias precisa ler o cookie de escolha,
        // entao ele nao pode sair cifrado.
        $middleware->encryptCookies(except: [AvisoDeCookies::COOKIE]);

        // Decide o que pode entrar no HTML antes de qualquer view renderizar.
        $middleware->web(append: [LeConsentimentoDeCookies::class, CabecalhosDeSeguranca::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
