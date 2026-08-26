<?php

namespace App\Http\Controllers;

use App\Services\Site;
use Illuminate\View\View;

/**
 * Site publico. As paginas sao fixas em codigo (doc: Mapa do Site); o que
 * vem do painel e so contato, endereco, redes e o corpo dos dois documentos
 * legais.
 */
class SiteController extends Controller
{
    /** O nome da view vem de `defaults()` na rota, nunca da URL. */
    public function pagina(string $pagina): View
    {
        return view("site.{$pagina}");
    }

    /**
     * Politica de Privacidade e Termos de Uso. Renderiza a versao
     * publicada: rascunho salvo no painel nao muda a pagina publica.
     */
    public function textoLegal(string $chave): View
    {
        return view("site.{$chave}", [
            'documento' => Site::textoLegal($chave) ?? [],
            'email' => Site::dados()->email,
        ]);
    }
}
