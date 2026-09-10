@extends('layouts.site', [
    'titulo' => 'Livros — Dra. Marina Mariz',
    'descricao' => 'Publicações autorais da Dra. Marina Mariz: obras sobre gestação, alto risco e puerpério, em linguagem acessível para gestantes e profissionais.',
    'canonical' => 'https://dramarinamariz.com.br/educacao/livros',
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/planejamento.webp',
])

@section('conteudo')
<!-- ===== cena 1 — herói ===== -->
<!-- textos: Escopo das Páginas/Livros.md, seção 1 -->
<section class="scene lvh scheme-04" id="livros" data-scene data-tone="dark" aria-labelledby="livros-titulo">
  <div class="lvh__halo" aria-hidden="true"></div>
  <div class="lvh__grain" aria-hidden="true"></div>
  <div class="scene__body">
    <div>
      <nav class="crumbs rise" aria-label="Trilha">
        <a href="/educacao">Educação</a>
        <i class="ph ph-caret-right" aria-hidden="true"></i>
        <span aria-current="page">Livros</span>
      </nav>
      <h1 class="t-h1 rise" data-d="1" data-split id="livros-titulo">Publicações autorais</h1>
      <p class="lvh__lead rise" data-d="2"><strong>Anos de prática clínica</strong> traduzidos em <strong>conhecimento acessível</strong>.<br>Obras que acompanham gestantes e profissionais em cada fase da jornada.</p>
      <p class="lvh__lead rise" data-d="2">Cada título nasceu de uma pergunta que se repete no consultório.<br><strong>Linguagem acessível</strong>, sem abrir mão do <strong>rigor técnico</strong>.</p>
      <div class="lvh__actions rise" data-d="3">
        <a class="btn btn--primary" href="#lista"><span class="btn__label">Ver as publicações <i class="ph ph-arrow-down" aria-hidden="true"></i></span></a>
        <a class="btn btn--outline" href="/educacao"><span class="btn__label">Tudo em Educação <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a>
      </div>
    </div>

    <!-- as capas dos três primeiros itens publicados, em perspectiva.
         Sem catálogo publicado a pilha vinha vazia e o herói ficava com um
         buraco: o estado vazio cai nas fotos decorativas da página. -->
    <div class="stack rise rise--right" data-d="2" aria-hidden="true">
@forelse ($itens->take(3)->filter->imagem as $capa)
      <figure><img src="{{ $capa->imagem->url() }}" alt="" width="{{ $capa->imagem->width }}" height="{{ $capa->imagem->height }}" loading="lazy" decoding="async"></figure>
@empty
      <figure><img src="/assets/img/livros-gestacao.webp" alt="" width="800" height="1198" loading="lazy" decoding="async"></figure>
      <figure><img src="/assets/img/consultorio.webp" alt="" width="1066" height="1600" loading="lazy" decoding="async"></figure>
      <figure><img src="/assets/img/livros-puerperio.webp" alt="" width="800" height="1196" loading="lazy" decoding="async"></figure>
@endforelse
    </div>
  </div>
</section>

<!-- ===== cena 2 — as publicações ===== -->
<!-- textos: Escopo das Páginas/Livros.md, seção 2 · itens: Painel/01 tipo=livro -->
<section class="scene lvl scheme-05" id="lista" data-scene data-tone="light" aria-label="As publicações">
  <div class="scene__body">
@forelse ($itens as $item)
@if ($loop->first)
      <ol class="lvl__grid">
@endif
        <li class="rise" data-d="1"><article class="book card">
          <figure class="book__shot">
@if ($item->imagem)
            <img src="{{ $item->imagem->url() }}" alt="{{ $item->imagem->is_decorative ? '' : $item->imagem->alt }}" width="{{ $item->imagem->width }}" height="{{ $item->imagem->height }}" loading="lazy" decoding="async">
@endif
          </figure>
          <h3 class="book__title">{{ $item->title }}</h3>
          @include('site.educacao.partes.meta', ['meta' => [$item->year, $item->publisher]])
          <p class="book__desc">{{ $item->description }}</p>
          <div class="book__acoes">
@if (filled($item->external_url))
            <a class="btn btn--primary book__buy" href="{{ $item->external_url }}" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-shopping-cart-simple" aria-hidden="true"></i> Comprar</span></a>
@endif
            <button class="btn btn--outline book__mais" type="button" data-abre="livro-{{ $item->getKey() }}"><span class="btn__label">Saber mais</span></button>
          </div>
        </article></li>
@if ($loop->last)
      </ol>
@endif
@empty
      @include('site.educacao.partes.vazio', [
          'icone' => 'ph-books',
          'titulo' => 'Em breve novas publicações',
          'texto' => 'Ainda não há livro publicado por aqui. Assine para saber assim que o primeiro sair.',
          'rotulo' => 'Quero ser avisada',
      ])
@endforelse

      <!-- "Saber mais": um <dialog> por item, aberto pelo botão do card -->
@foreach ($itens as $item)
    <dialog class="modal livro" id="livro-{{ $item->getKey() }}" aria-labelledby="livro-{{ $item->getKey() }}-titulo">
      <button class="modal__x" type="button" data-fecha aria-label="Fechar"><i class="ph ph-x" aria-hidden="true"></i></button>
      <div class="livro__corpo">
        <div class="livro__lado">
@if ($item->imagem)
          <img src="{{ $item->imagem->url() }}" alt="{{ $item->imagem->is_decorative ? '' : $item->imagem->alt }}" width="{{ $item->imagem->width }}" height="{{ $item->imagem->height }}" loading="lazy" decoding="async">
@endif
@if (filled($item->external_url))
          <a class="btn btn--primary" href="{{ $item->external_url }}" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-shopping-cart-simple" aria-hidden="true"></i> Comprar</span></a>
@endif
        </div>
        <div class="livro__texto">
          <h3 class="t-h3" id="livro-{{ $item->getKey() }}-titulo">{{ $item->title }}</h3>
          @include('site.educacao.partes.meta', ['meta' => [$item->year, $item->publisher]])
          @include('site.educacao.partes.texto', ['item' => $item])
        </div>
      </div>
    </dialog>
@endforeach

    @include('site.educacao.partes.mais', ['itens' => $itens])
  </div>
</section>

<!-- ===== cena 3 — quero ser avisada ===== -->
<!-- textos: Escopo das Páginas/Livros.md, seção 3 · destino: Painel/04 — Newsletter -->
<section class="scene lvn scheme-04" id="avisos" data-scene data-tone="dark" aria-labelledby="avisos-titulo">
  <div class="scene__body">
    <div>
      <h2 class="t-h2 rise" id="avisos-titulo">Quer saber quando será <br class="br-desk">publicado o próximo livro?</h2>
      <p class="lvn__lead rise" data-d="1">Receba <strong>lançamentos</strong> e conteúdos em primeira mão.</p>
    </div>

    @include('site.educacao.partes.newsletter', [
        'chave' => 'newsletter-livros',
        'origem' => 'livros',
        'prefixo' => 'lv',
        'rotulo' => 'Quero ser avisada',
    ])
  </div>
</section>
@endsection

@section('pos-rodape')
@include('site.partials.barra')
@endsection
