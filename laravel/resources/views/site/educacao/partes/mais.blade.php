{{--
    Carregamento infinito das listas de Educacao (escopos das paginas).

    6 itens por vez, e o bloco so existe acima de 6 publicados. Nada de
    numeros de pagina: o servidor continua paginando em ?pagina=N, e e este
    link que o rolar consome e que responde para quem esta sem JavaScript.
--}}
@if ($itens->hasMorePages())
      <div class="mais" data-infinito data-proxima="{{ $itens->nextPageUrl() }}" data-alvo=".lvl__grid">
        <a class="btn btn--outline mais__btn" href="{{ $itens->nextPageUrl() }}"><span class="btn__label">Carregar mais <i class="ph ph-arrow-down" aria-hidden="true"></i></span></a>
        <p class="mais__fim" hidden>Você chegou ao fim da lista</p>
      </div>
@endif
