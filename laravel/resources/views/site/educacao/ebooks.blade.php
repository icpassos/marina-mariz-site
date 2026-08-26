@extends('layouts.site', [
    'titulo' => 'E-books — Dra. Marina Mariz',
    'descricao' => 'E-books da Dra. Marina Mariz: guias práticos e aprofundados sobre gestação, alto risco e puerpério, baseados em evidência.',
    'canonical' => 'https://dramarinamariz.com.br/educacao/ebooks',
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/saude-da-mulher.webp',
])

@php
    // "PDF", "EPUB" ou "PDF + EPUB": o formato vem dos arquivos enviados,
    // nao de um campo escrito a mao no painel.
    $formato = fn ($item) => implode(' + ', array_filter([$item->pdf ? 'PDF' : null, $item->epub ? 'EPUB' : null])) ?: null;
@endphp

@section('conteudo')
<!-- ===== cena 1 — herói ===== -->
<!-- textos: Escopo das Páginas/E-books.md, seção 1 -->
<section class="scene lvh scheme-04" id="ebooks" data-scene data-tone="dark" aria-labelledby="ebooks-titulo">
  <div class="lvh__halo" aria-hidden="true"></div>
  <div class="lvh__grain" aria-hidden="true"></div>
  <div class="scene__body">
    <div>
      <nav class="crumbs rise" aria-label="Trilha">
        <a href="/educacao">Educação</a>
        <i class="ph ph-caret-right" aria-hidden="true"></i>
        <span aria-current="page">E-books</span>
      </nav>
      <h1 class="t-h1 rise" data-d="1" data-split id="ebooks-titulo">Conteúdo<br>digital exclusivo</h1>
      <p class="lvh__lead rise" data-d="2">Guias práticos, aprofundados e <strong>baseados em evidência</strong> —<br>para quem busca informação de <strong>qualidade sem sair de casa</strong>.</p>
      <p class="lvh__lead rise" data-d="2">Cada e-book nasce <strong>das perguntas que se repetem no<br>consultório</strong> e entrega o assunto inteiro, em vez de um pedaço.</p>
      <div class="lvh__actions rise" data-d="3">
        <a class="btn btn--primary" href="#lista"><span class="btn__label">Ver os e-books <i class="ph ph-arrow-down" aria-hidden="true"></i></span></a>
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
      <figure><img src="/assets/img/ebooks-leitura-digital.webp" alt="" width="1200" height="1600" loading="lazy" decoding="async"></figure>
      <figure><img src="/assets/img/ebooks-gestante-leitura.webp" alt="" width="1200" height="1600" loading="lazy" decoding="async"></figure>
      <figure><img src="/assets/img/ebooks-tablet.webp" alt="" width="1200" height="1600" loading="lazy" decoding="async"></figure>
@endforelse
    </div>
  </div>
</section>

<!-- ===== cena 2 — lista ===== -->
<!-- textos: Escopo das Páginas/E-books.md, seção 2 · itens: Painel/01 — Catálogo Educação -->
<section class="scene lvl scheme-05" id="lista" data-scene data-tone="light" aria-label="Os e-books">
    <div class="scene__body">
      @include('site.educacao.partes.retorno-material')
@forelse ($itens as $item)
@if ($loop->first)
      <ol class="lvl__grid">
@endif
        <li class="rise" data-d="1"><article class="book card">
          <figure class="book__shot">
@if ($item->imagem)
            <img src="{{ $item->imagem->url() }}" alt="{{ $item->imagem->is_decorative ? '' : $item->imagem->alt }}" width="{{ $item->imagem->width }}" height="{{ $item->imagem->height }}" loading="lazy" decoding="async">
@endif
@if ($item->is_free)
            <span class="book__selo">Gratuito</span>
@endif
          </figure>
          <h3 class="book__title">{{ $item->title }}</h3>
          @include('site.educacao.partes.meta', ['meta' => [$formato($item)]])
          <p class="book__desc">{{ $item->description }}</p>
          <div class="book__acoes">
@if ($item->is_free && $item->comoMaterial()['arquivo'])
            <button class="btn btn--primary book__buy" type="button" data-abre="baixar-{{ $item->getKey() }}"><span class="btn__label"><i class="ph ph-download-simple" aria-hidden="true"></i> Baixar grátis</span></button>
@elseif (filled($item->external_url))
            <a class="btn btn--primary book__buy" href="{{ $item->external_url }}" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-shopping-cart-simple" aria-hidden="true"></i> Comprar</span></a>
@endif
            <button class="btn btn--outline book__mais" type="button" data-abre="ebook-{{ $item->getKey() }}"><span class="btn__label">Saber mais</span></button>
          </div>
        </article></li>
@if ($loop->last)
      </ol>
@endif
@empty
      @include('site.educacao.partes.vazio', [
          'icone' => 'ph-file-text',
          'titulo' => 'Em breve',
          'texto' => 'Ainda não há e-book publicado por aqui. Assine para saber assim que o primeiro sair.',
          'rotulo' => 'Quero receber novidades',
      ])
@endforelse

@foreach ($itens as $item)
    <!-- ficha do item; o botão da ficha repete o botão do card -->
    <dialog class="modal livro" id="ebook-{{ $item->getKey() }}" aria-labelledby="ebook-{{ $item->getKey() }}-titulo">
      <button class="modal__x" type="button" data-fecha aria-label="Fechar"><i class="ph ph-x" aria-hidden="true"></i></button>
      <div class="livro__corpo">
        <div class="livro__lado">
@if ($item->imagem)
          <img src="{{ $item->imagem->url() }}" alt="" width="{{ $item->imagem->width }}" height="{{ $item->imagem->height }}" loading="lazy" decoding="async">
@endif
@if ($item->is_free && $item->comoMaterial()['arquivo'])
          <button class="btn btn--primary" type="button" data-abre="baixar-{{ $item->getKey() }}"><span class="btn__label"><i class="ph ph-download-simple" aria-hidden="true"></i> Baixar grátis</span></button>
@elseif (filled($item->external_url))
          <a class="btn btn--primary" href="{{ $item->external_url }}" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-shopping-cart-simple" aria-hidden="true"></i> Comprar</span></a>
@endif
        </div>
        <div class="livro__texto">
          <h3 class="t-h3" id="ebook-{{ $item->getKey() }}-titulo">{{ $item->title }}</h3>
          @include('site.educacao.partes.meta', ['meta' => [$formato($item), $item->is_free ? 'Gratuito' : null]])
          @include('site.educacao.partes.texto', ['item' => $item])
        </div>
      </div>
    </dialog>
@if ($item->is_free && $item->comoMaterial()['arquivo'])
    <!-- janela de download do e-book gratuito — a mesma de Materiais Gratuitos -->
    @include('site.educacao.partes.baixar', ['item' => $item, 'id' => 'baixar-'.$item->getKey()])
@endif
@endforeach

    @include('site.educacao.partes.mais', ['itens' => $itens])
  </div>
</section>

<!-- ===== cena 3 — como funciona ===== -->
<!-- textos: Escopo das Páginas/E-books.md, seção 3 -->
<section class="scene lvp scheme-05" id="como-funciona" data-scene data-tone="light" aria-labelledby="como-titulo">
  <div class="scene__body">
    <h2 class="t-h2 rise" id="como-titulo">Como funciona</h2>
    <ol class="passos rise" data-d="1">
      <li class="passo"><span class="passo__n">01</span><strong>Escolha o e-book</strong><p>Cada ficha mostra formato, tamanho e o que está dentro.</p></li>
      <li class="passo"><span class="passo__n">02</span><strong>Finalize a compra</strong><p>Ou informe apenas o seu e-mail, quando o material for gratuito.</p></li>
      <li class="passo"><span class="passo__n">03</span><strong>Receba o link</strong><p>Ele aparece na tela assim que o banco confirma. A cópia por e-mail sai pela fila e pode levar alguns minutos.</p></li>
    </ol>
  </div>
</section>

<!-- ===== cena final — newsletter ===== -->
<!-- textos: Escopo das Páginas/E-books.md, seção 4 · destino: Painel/04 — Newsletter
     lista única de newsletter; o campo origem diz de qual página veio a inscrição -->
<section class="scene lvn scheme-04" id="avisos" data-scene data-tone="dark" aria-labelledby="avisos-titulo">
  <div class="scene__body">
    <div>
      <h2 class="t-h2 rise" id="avisos-titulo">Quer saber quando sai o próximo?</h2>
      <p class="lvn__lead rise" data-d="1">Lançamentos, materiais novos e conteúdo em primeira mão —<br>de e-books a episódios do podcast.</p>
    </div>

    @include('site.educacao.partes.newsletter', [
        'chave' => 'newsletter-ebooks',
        'origem' => 'ebooks',
        'prefixo' => 'eb',
        'rotulo' => 'Quero receber novidades',
    ])
  </div>
</section>
@endsection

@section('pos-rodape')
@include('site.partials.barra')
@endsection
