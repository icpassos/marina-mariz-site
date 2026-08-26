@extends('layouts.site', [
    'titulo' => 'Materiais gratuitos — Dra. Marina Mariz',
    'descricao' => 'Guias, checklists e planilhas gratuitos da Dra. Marina Mariz para quem está começando a se informar sobre gestação e puerpério.',
    'canonical' => 'https://dramarinamariz.com.br/educacao/materiais-gratuitos',
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/maos.webp',
])

@php
    $tamanho = function ($media): ?string {
        $bytes = $media?->size;

        if (! $bytes) {
            return null;
        }

        $unidades = ['B', 'KB', 'MB', 'GB'];
        $i = min((int) floor(log($bytes, 1024)), 3);
        $valor = round($bytes / (1024 ** $i), $i >= 2 ? 1 : 0);

        return str_replace('.', ',', (string) $valor).' '.$unidades[$i];
    };

    // E-book gratuito entra aqui com Formato = PDF, Arquivo = o .pdf e
    // Exige e-mail = sim, sem cadastro novo (doc 01).
    $dados = fn ($item) => [
        $item->comoMaterial()['formato']?->getLabel(),
        $tamanho($item->comoMaterial()['arquivo']),
    ];
@endphp

@section('conteudo')
<!-- ===== cena 1 — herói ===== -->
<!-- textos: Escopo das Páginas/Materiais Gratuitos.md, seção 1 -->
<section class="scene lvh scheme-04" id="materiais" data-scene data-tone="dark" aria-labelledby="materiais-titulo">
  <div class="lvh__halo" aria-hidden="true"></div>
  <div class="lvh__grain" aria-hidden="true"></div>
  <div class="scene__body">
    <div>
      <nav class="crumbs rise" aria-label="Trilha">
        <a href="/educacao">Educação</a>
        <i class="ph ph-caret-right" aria-hidden="true"></i>
        <span aria-current="page">Materiais gratuitos</span>
      </nav>
      <h1 class="t-h1 rise" data-d="1" data-split id="materiais-titulo">Um primeiro<br>passo generoso</h1>
      <p class="lvh__lead rise" data-d="2"><strong>Guias, checklists e conteúdos</strong> introdutórios<br>para quem está começando a se informar.<br>De graça, sem pegadinha.</p>
      <div class="lvh__actions rise" data-d="3">
        <a class="btn btn--primary" href="#lista"><span class="btn__label">Ver os materiais <i class="ph ph-arrow-down" aria-hidden="true"></i></span></a>
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
      <figure><img src="/assets/img/materiais-anotacoes.webp" alt="" width="1200" height="1600" loading="lazy" decoding="async"></figure>
      <figure><img src="/assets/img/materiais-checklist.webp" alt="" width="1200" height="1600" loading="lazy" decoding="async"></figure>
      <figure><img src="/assets/img/materiais-gestante-celular.webp" alt="" width="1200" height="1600" loading="lazy" decoding="async"></figure>
@endforelse
    </div>
  </div>
</section>

<!-- ===== cena 2 — lista ===== -->
<!-- textos: Escopo das Páginas/Materiais Gratuitos.md, seção 2 · itens: Painel/01 — Catálogo Educação -->
<section class="scene lvl scheme-05" id="lista" data-scene data-tone="light" aria-label="Os materiais gratuitos">
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
            <span class="book__selo">Gratuito</span>
          </figure>
          <h3 class="book__title">{{ $item->title }}</h3>
          @include('site.educacao.partes.meta', ['meta' => $dados($item)])
          <p class="book__desc">{{ $item->description }}</p>
          <div class="book__acoes">
@if ($item->comoMaterial()['arquivo'])
@if ($item->comoMaterial()['exige_email'])
            <button class="btn btn--primary book__buy" type="button" data-abre="material-{{ $item->getKey() }}"><span class="btn__label"><i class="ph ph-download-simple" aria-hidden="true"></i> Baixar grátis</span></button>
@else
            {{-- Sem "exige e-mail", baixa direto: sem janela e sem lead. --}}
            <a class="btn btn--primary book__buy" href="{{ $item->linkDeDownload() }}" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-download-simple" aria-hidden="true"></i> Baixar grátis</span></a>
@endif
@endif
            <button class="btn btn--outline book__mais" type="button" data-abre="ficha-{{ $item->getKey() }}"><span class="btn__label">Saber mais</span></button>
          </div>
        </article></li>
@if ($loop->last)
      </ol>
@endif
@empty
      @include('site.educacao.partes.vazio', [
          'icone' => 'ph-download-simple',
          'titulo' => 'Em breve',
          'texto' => 'Ainda não há material publicado por aqui. Assine para saber assim que o primeiro sair.',
          'rotulo' => 'Quero receber novidades',
      ])
@endforelse

@foreach ($itens as $item)
    <dialog class="modal livro" id="ficha-{{ $item->getKey() }}" aria-labelledby="ficha-{{ $item->getKey() }}-titulo">
      <button class="modal__x" type="button" data-fecha aria-label="Fechar"><i class="ph ph-x" aria-hidden="true"></i></button>
      <div class="livro__corpo">
        <div class="livro__lado">
@if ($item->imagem)
          <img src="{{ $item->imagem->url() }}" alt="" width="{{ $item->imagem->width }}" height="{{ $item->imagem->height }}" loading="lazy" decoding="async">
@endif
@if ($item->comoMaterial()['arquivo'])
@if ($item->comoMaterial()['exige_email'])
          <button class="btn btn--primary" type="button" data-abre="material-{{ $item->getKey() }}"><span class="btn__label"><i class="ph ph-download-simple" aria-hidden="true"></i> Baixar grátis</span></button>
@else
          <a class="btn btn--primary" href="{{ $item->linkDeDownload() }}" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-download-simple" aria-hidden="true"></i> Baixar grátis</span></a>
@endif
@endif
        </div>
        <div class="livro__texto">
          <h3 class="t-h3" id="ficha-{{ $item->getKey() }}-titulo">{{ $item->title }}</h3>
          @include('site.educacao.partes.meta', ['meta' => [...$dados($item), 'Gratuito']])
          @include('site.educacao.partes.texto', ['item' => $item])
        </div>
      </div>
    </dialog>
@if ($item->comoMaterial()['arquivo'] && $item->comoMaterial()['exige_email'])
    @include('site.educacao.partes.baixar', ['item' => $item, 'id' => 'material-'.$item->getKey()])
@endif
@endforeach

    @include('site.educacao.partes.mais', ['itens' => $itens])
  </div>
</section>

<!-- ===== cena 3 — como chega até você ===== -->
<!-- textos: Escopo das Páginas/Materiais Gratuitos.md, seção 3 -->
<section class="scene lvp scheme-05" id="como-funciona" data-scene data-tone="light" aria-labelledby="como-titulo">
  <div class="scene__body">
    <h2 class="t-h2 rise" id="como-titulo">Como o material chega até você</h2>
    <ol class="passos rise" data-d="1">
      <li class="passo"><span class="passo__n">01</span><strong>Clique em baixar</strong><p>Abre uma janela pedindo nome, e-mail e a ciência da Política de Privacidade.</p></li>
      <li class="passo"><span class="passo__n">02</span><strong>Baixa na hora</strong><p>O arquivo abre em uma nova aba e baixa sozinho. Nada vai por e-mail.</p></li>
      <li class="passo"><span class="passo__n">03</span><strong>Sem pegadinha</strong><p>Receber novidades é opcional e vem desmarcado. Baixar de novo não cria cadastro duplicado.</p></li>
    </ol>
  </div>
</section>

<!-- ===== cena final — newsletter ===== -->
<!-- textos: Escopo das Páginas/Materiais Gratuitos.md, seção 4 · destino: Painel/04 — Newsletter
     lista única de newsletter; o campo origem diz de qual página veio a inscrição -->
<section class="scene lvn scheme-04" id="avisos" data-scene data-tone="dark" aria-labelledby="avisos-titulo">
  <div class="scene__body">
    <div>
      <h2 class="t-h2 rise" id="avisos-titulo">Quer receber os<br>próximos materiais?</h2>
      <p class="lvn__lead rise" data-d="1">Guias, checklists e conteúdos novos chegam por e-mail<br>assim que saem — sem pegadinha, como este aqui.</p>
    </div>

    @include('site.educacao.partes.newsletter', [
        'chave' => 'newsletter-materiais',
        'origem' => 'materiais',
        'prefixo' => 'mg',
        'rotulo' => 'Quero receber novidades',
    ])
  </div>
</section>
@endsection

@section('pos-rodape')
@include('site.partials.barra')
@endsection
