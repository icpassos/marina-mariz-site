<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\Site;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

/**
 * Site publico. As paginas sao fixas em codigo (doc: Mapa do Site); o que
 * vem do painel e so contato, endereco, redes e o corpo dos dois documentos
 * legais.
 */
class SiteController extends Controller
{
    /** Quantos posts a home mostra na cena do blog. */
    private const POSTS_NA_HOME = 6;

    /** O nome da view vem de `defaults()` na rota, nunca da URL. */
    public function pagina(string $pagina): View
    {
        // So a home lista post; as outras paginas sao fixas em codigo.
        return view("site.{$pagina}", $pagina === 'index'
            ? ['ultimosPosts' => $this->ultimosPosts()]
            : []);
    }

    /** Mesmo recorte publico do blog: `noAr()`, mais novo primeiro. */
    private function ultimosPosts(): Collection
    {
        return Post::noAr()
            ->with('categoria')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(self::POSTS_NA_HOME)
            ->get();
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
