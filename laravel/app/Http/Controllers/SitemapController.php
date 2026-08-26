<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

/**
 * Sitemap do site. Gerado na hora, e nao um arquivo escrito a mao: com 270
 * posts vindos do WordPress e novos entrando pelo painel, uma lista fixa
 * nasceria desatualizada.
 *
 * Sai so o que esta no ar: rascunho, agendado e arquivado nunca aparecem,
 * pelo mesmo recorte que o blog usa.
 */
class SitemapController extends Controller
{
    /** Paginas fixas do site, com o peso que ja estava no sitemap escrito. */
    private const PAGINAS = [
        '/' => '1.0',
        '/sobre' => '0.9',
        '/especialidades' => '0.9',
        '/contato' => '0.9',
        '/amara' => '0.8',
        '/podcast' => '0.8',
        '/blog' => '0.8',
        '/newsletter' => '0.6',
        '/educacao' => '0.8',
        '/educacao/cursos' => '0.7',
        '/educacao/ebooks' => '0.7',
        '/educacao/eventos' => '0.7',
        '/educacao/formacao-profissional' => '0.7',
        '/educacao/livros' => '0.7',
        '/educacao/materiais-gratuitos' => '0.7',
        '/politica-de-privacidade' => '0.7',
        '/termos-de-uso' => '0.7',
    ];

    public function __invoke(): Response
    {
        $urls = [];

        foreach (self::PAGINAS as $caminho => $peso) {
            $urls[] = ['loc' => url($caminho), 'peso' => $peso, 'data' => null];
        }

        foreach (Post::noAr()->orderByDesc('published_at')->get(['slug', 'updated_at']) as $post) {
            $urls[] = [
                'loc' => route('site.blog.post', $post->slug),
                'peso' => '0.6',
                'data' => $post->updated_at?->toDateString(),
            ];
        }

        return response()
            ->view('site.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
