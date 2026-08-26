<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Services\Site;
use Illuminate\View\View;

/**
 * Pagina /links: o agregador de "link na bio" (doc 09).
 *
 * Vive fora do site — sem menu, sem rodape, sem entrada no sitemap. Quem
 * chega vem do perfil no Instagram, e o unico caminho de volta e um botao
 * que a propria Marina cadastra.
 */
class LinksController extends Controller
{
    public function __invoke(): View
    {
        return view('site.links', [
            'links' => Link::noAr()->with('imagem')->get(),
            'dados' => Site::dados(),
        ]);
    }
}
