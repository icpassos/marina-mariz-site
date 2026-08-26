{{--
    Catalogo sem item publicado (estados.html, secao Listas vazias). Nunca
    uma grade em branco: o vazio e espera, e o texto diz isso.
--}}
      <div class="lvl__empty">
        <span class="lvl__empty-ico"><i class="ph {{ $icone }}" aria-hidden="true"></i></span>
        <h3 class="t-h3">{{ $titulo }}</h3>
        <p>{{ $texto }}</p>
        <a class="btn btn--primary" href="#avisos"><span class="btn__label">{{ $rotulo }} <i class="ph ph-arrow-down" aria-hidden="true"></i></span></a>
      </div>
