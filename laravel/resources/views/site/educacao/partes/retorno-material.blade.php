{{--
    Recibo do download (estados.html, secao Download). O <dialog> nao pode
    reabrir sozinho sem JavaScript, entao o recibo aparece no alto da lista,
    onde a pessoa estava, e recebe o foco.

    Ou entrega, ou nao entrega: sem link gerado, o botao nao aparece.
--}}
@if (session('download_url'))
      <p class="bln__msg bln__msg--ok" role="status" tabindex="-1" autofocus><i class="ph ph-check-circle" aria-hidden="true"></i>Seu material está baixando em uma nova aba.</p>
      <a class="btn btn--primary" href="{{ session('download_url') }}" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-download-simple" aria-hidden="true"></i> Baixar agora</span></a>
@endif
