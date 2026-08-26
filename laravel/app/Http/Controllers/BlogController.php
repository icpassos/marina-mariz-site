<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Blog publico (docs: Escopo das Paginas/Blog e Painel/02 — Blog).
 *
 * O recorte e sempre `status = publicado AND published_at <= NOW()`, o
 * mesmo em lista, busca, post e relacionados. Rascunho, agendado,
 * arquivado e lixeira respondem 404 padrao — sem redirect e sem 410.
 *
 * A busca nao grava nada: nao existe tabela de termo pesquisado, contador
 * nem log. E consulta e resposta, so isso (doc 02).
 */
class BlogController extends Controller
{
    /** 12 por pagina, cada uma com endereco proprio. Rolagem infinita e proibida pela spec. */
    public const POR_PAGINA = 12;

    private const BASE = 'https://dramarinamariz.com.br/blog';

    public function lista(Request $request): View
    {
        $termo = trim((string) $request->query('q', ''));

        // Slug desconhecido nao filtra nada em vez de esconder o blog inteiro.
        $categoria = Category::query()
            ->where('slug', trim((string) $request->query('categoria', '')))
            ->first();

        $posts = Post::noAr()
            ->with(['categoria', 'imagem'])
            ->when($categoria, fn (Builder $q) => $q->where('category_id', $categoria->getKey()))
            ->when(filled($termo), fn (Builder $q) => $q->where(
                fn (Builder $b) => $this->buscar($b, $termo)
            ))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(self::POR_PAGINA, ['*'], 'pagina')
            ->withQueryString()
            ->onEachSide(1);

        return view('site.blog', [
            'posts' => $posts,
            'termo' => $termo,
            'categoriaAtual' => $categoria,
            'categorias' => $this->abas(),
            'totalNoAr' => Post::noAr()->count(),
            'canonical' => $this->canonical($termo, $categoria?->slug, $posts),
        ]);
    }

    public function post(string $slug): View
    {
        $post = Post::noAr()
            ->with(['categoria', 'imagem', 'seoImagem', 'arquivo'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('site.blog-post', [
            'post' => $post,
            'relacionados' => $post->relacionadosNoAr()->with(['categoria', 'imagem'])->get(),
        ]);
    }

    /**
     * Endereco do WordPress antigo. O post continua o mesmo, so mudou de
     * lugar: 301 preserva o que o Google ja indexou e o link que alguem
     * guardou. Slug sem post no ar cai no 404 padrao.
     */
    public function permalinkAntigo(string $ano, string $mes, string $dia, string $slug): RedirectResponse
    {
        abort_unless(Post::noAr()->where('slug', $slug)->exists(), 404);

        return redirect()->route('site.blog.post', $slug, status: 301);
    }

    /**
     * As abas sao exatamente as categorias com post no ar, na ordem do
     * painel. Nao ha lista escrita em codigo, e categoria vazia nao vira
     * aba (doc 02). A mesma lista serve de atalho no estado sem resultado.
     *
     * @return Collection<int, Category>
     */
    private function abas(): Collection
    {
        return Category::query()
            ->whereHas('posts', fn (Builder $q) => $q->noAr())
            ->withCount(['posts as posts_no_ar_count' => fn (Builder $q) => $q->noAr()])
            ->orderBy('position')
            ->orderBy('id')
            ->get();
    }

    /**
     * Titulo, descricao e conteudo, com o recorte publico ja aplicado por
     * fora. O conteudo rico e JSON com acento escapado (`ç`), entao o
     * termo tambem entra na forma escapada — senao "gestacao" com cedilha
     * nunca casaria com o corpo do post.
     */
    private function buscar(Builder $consulta, string $termo): void
    {
        $consulta->where('title', 'like', $this->padrao($termo))
            ->orWhere('description', 'like', $this->padrao($termo))
            ->orWhere('content', 'like', $this->padrao($termo));

        $escapado = trim((string) json_encode($termo), '"');

        if ($escapado !== $termo) {
            $consulta->orWhere('content', 'like', $this->padrao($escapado));
        }
    }

    /** `%`, `_` e a contrabarra sao literais na busca, nao curinga. */
    private function padrao(string $termo): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $termo).'%';
    }

    /**
     * Cada recorte tem endereco proprio e canonical proprio — e o que
     * torna a busca e a pagina 2 compartilhaveis e indexaveis. Parametro
     * que o blog nao usa fica de fora.
     *
     * @param  LengthAwarePaginator<int, Post>  $posts
     */
    private function canonical(string $termo, ?string $categoria, LengthAwarePaginator $posts): string
    {
        $parametros = array_filter([
            'q' => $termo,
            'categoria' => $categoria,
            'pagina' => $posts->currentPage() > 1 ? $posts->currentPage() : null,
        ]);

        return $parametros === [] ? self::BASE : self::BASE.'?'.http_build_query($parametros);
    }
}
