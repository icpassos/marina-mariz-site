<?php

namespace App\Http\Controllers;

use App\Enums\EducationType;
use App\Models\EducationItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

/**
 * As sete paginas publicas de Educacao (doc 01 e escopos das paginas).
 *
 * Nenhum item tem pagina propria: a lista mostra o card, a ficha e um
 * pop-up e o botao manda para o link externo. O hub `/educacao` nao tem
 * conteudo proprio — so distribui.
 */
class EducacaoSiteController extends Controller
{
    /** 6 por vez, como manda o escopo. O rolar infinito consome ?pagina=N. */
    public const POR_PAGINA = 6;

    /** Endereco => tipo do catalogo. E tambem a lista de rotas. */
    public const SECOES = [
        'livros' => EducationType::Livro,
        'ebooks' => EducationType::Ebook,
        'cursos' => EducationType::Curso,
        'eventos' => EducationType::Evento,
        'materiais-gratuitos' => EducationType::Material,
        'formacao-profissional' => EducationType::Formacao,
    ];

    public function hub(): View
    {
        return view('site.educacao.index');
    }

    public function lista(string $secao): View
    {
        $tipo = self::SECOES[$secao];

        return view("site.educacao.{$secao}", [
            'itens' => $this->itens($tipo),
            // Evento sai de "proximos" sozinho quando a data de fim passa:
            // e o relogio que move o card, nao alguem no painel.
            'passados' => $tipo === EducationType::Evento
                ? EducationItem::paraSite($tipo)->with('imagem')
                    ->where('ends_at', '<', now())
                    ->reorder('starts_at', 'desc')
                    ->get()
                : collect(),
        ]);
    }

    private function itens(EducationType $tipo): LengthAwarePaginator
    {
        $consulta = EducationItem::paraSite($tipo)->with(['imagem', 'arquivo', 'pdf', 'epub']);

        if ($tipo === EducationType::Evento) {
            $consulta->where('ends_at', '>=', now());
        }

        return $consulta->paginate(self::POR_PAGINA, ['*'], 'pagina')->withQueryString();
    }
}
