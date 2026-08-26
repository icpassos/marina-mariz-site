{{-- Paginacao com endereco proprio: /blog?pagina=2 · /blog?categoria=artigos&pagina=2.
     Marcacao de estados.html, secao Paginacao. O "anterior" fica desabilitado
     em vez de sumir — a barra nao muda de largura ao navegar. --}}
@if ($paginator->hasPages())
<nav aria-label="Paginação">
  <ol class="pager">
    <li>
      @if ($paginator->onFirstPage())
      <span class="pager__off" aria-disabled="true"><i class="ph ph-caret-left" aria-hidden="true"></i></span>
      @else
      <a href="{{ $paginator->previousPageUrl() }}" aria-label="Página anterior"><i class="ph ph-caret-left" aria-hidden="true"></i></a>
      @endif
    </li>
    @foreach ($elements as $elemento)
      @if (is_string($elemento))
    <li><span class="pager__gap">…</span></li>
      @elseif (is_array($elemento))
        @foreach ($elemento as $pagina => $url)
    <li><a href="{{ $url }}"@if ($pagina == $paginator->currentPage()) aria-current="page"@endif>{{ $pagina }}</a></li>
        @endforeach
      @endif
    @endforeach
    <li>
      @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" aria-label="Próxima página"><i class="ph ph-caret-right" aria-hidden="true"></i></a>
      @else
      <span class="pager__off" aria-disabled="true"><i class="ph ph-caret-right" aria-hidden="true"></i></span>
      @endif
    </li>
  </ol>
</nav>
@endif
