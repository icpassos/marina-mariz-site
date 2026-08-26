<?php

namespace App\Http\Middleware;

use App\Support\AvisoDeCookies;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Le o cookie de consentimento antes de a view ser renderizada e decide o
 * que pode entrar no HTML. O padrao e nao permitir nada: sem escolha, sem
 * rastreamento.
 */
class LeConsentimentoDeCookies
{
    public function handle(Request $request, Closure $next): Response
    {
        $escolha = AvisoDeCookies::escolha($request);

        View::share([
            'cookiesEscolha' => $escolha,
            'analyticsPermitido' => $escolha['analiticos'] ?? false,
            'marketingPermitido' => $escolha['marketing'] ?? false,
        ]);

        return $next($request);
    }
}
