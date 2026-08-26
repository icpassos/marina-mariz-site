<?php

namespace App\Http\Controllers;

use App\Support\AvisoDeCookies;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Recebe a escolha do Aviso de Cookies. Formulario comum: funciona sem
 * JavaScript, e a pagina volta recarregada — que e justamente o momento em
 * que o servidor decide se algum script de rastreamento entra no HTML.
 */
class PreferenciasDeCookiesController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $escolha = $request->validate([
            'escolha' => ['required', 'in:aceitar,recusar,salvar'],
        ])['escolha'];

        $marcou = fn (string $categoria): bool => $escolha === 'aceitar'
            || ($escolha === 'salvar' && $request->boolean($categoria));

        AvisoDeCookies::registrar($request, $marcou('analiticos'), $marcou('marketing'));

        return back(fallback: '/');
    }
}
