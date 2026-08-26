@extends('layouts.site', [
    'titulo' => 'Cursos — Dra. Marina Mariz',
    'descricao' => 'Cursos da Dra. Marina Mariz para gestantes e casais: preparação para o parto, primeiros meses e gestação de alto risco.',
    'canonical' => 'https://dramarinamariz.com.br/educacao/cursos',
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/cursos-gestante.webp',
])

@php
    $periodo = fn ($item) => $item->starts_at
        ? $item->starts_at->format('d/m').($item->ends_at ? ' a '.$item->ends_at->format('d/m') : '')
        : null;
    $vagas = fn ($item) => $item->seats ? $item->seats.' vagas' : null;
    $dados = fn ($item) => [$item->format?->getLabel(), $item->workload, $periodo($item), $vagas($item)];
@endphp

@section('conteudo')
<!-- ===== cena 1 — herói ===== -->
<!-- textos: Escopo das Páginas/Cursos.md, seção 1 -->
<section class="scene lvh scheme-04" id="cursos" data-scene data-tone="dark" aria-labelledby="cursos-titulo">
  <div class="lvh__halo" aria-hidden="true"></div>
  <div class="lvh__grain" aria-hidden="true"></div>
  <div class="scene__body">
    <div>
      <nav class="crumbs rise" aria-label="Trilha">
        <a href="/educacao">Educação</a>
        <i class="ph ph-caret-right" aria-hidden="true"></i>
        <span aria-current="page">Cursos</span>
      </nav>
      <h1 class="t-h1 rise" data-d="1" data-split id="cursos-titulo">Formação para gestantes e casais</h1>
      <p class="lvh__lead rise" data-d="2"><strong>Conhecimento</strong> que transforma insegurança em preparo real.<br>Para quem quer viver a gestação com <strong>protagonismo</strong>.</p>
      <p class="lvh__lead rise" data-d="2"><strong>Turmas pequenas</strong>, material que fica com você e <strong>espaço<br>para as perguntas</strong> que ninguém faz em voz alta.</p>
      <div class="lvh__actions rise" data-d="3">
        <a class="btn btn--primary" href="#lista"><span class="btn__label">Ver os cursos <i class="ph ph-arrow-down" aria-hidden="true"></i></span></a>
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
      <figure><img src="/assets/img/cursos-casal.webp" alt="" width="1080" height="1620" loading="lazy" decoding="async"></figure>
      <figure><img src="/assets/img/cursos-gestante.webp" alt="" width="1080" height="1620" loading="lazy" decoding="async"></figure>
      <figure><img src="/assets/img/cursos-bebe.webp" alt="" width="1080" height="1620" loading="lazy" decoding="async"></figure>
@endforelse
    </div>
  </div>
</section>

<!-- ===== cena 2 — lista ===== -->
<!-- textos: Escopo das Páginas/Cursos.md, seção 2 · itens: Painel/01 — Catálogo Educação
     o botão usa o link externo: não existe página interna de curso -->
<section class="scene lvl scheme-05" id="lista" data-scene data-tone="light" aria-label="Os cursos">
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
          @include('site.educacao.partes.meta', ['meta' => $dados($item)])
          <p class="book__desc">{{ $item->description }}</p>
          <div class="book__acoes">
@if (filled($item->external_url))
            <a class="btn btn--primary book__buy" href="{{ $item->external_url }}" target="_blank" rel="noopener"><span class="btn__label">Ver o curso <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
@endif
            <button class="btn btn--outline book__mais" type="button" data-abre="curso-{{ $item->getKey() }}"><span class="btn__label">Saber mais</span></button>
          </div>
        </article></li>
@if ($loop->last)
      </ol>
@endif
@empty
      @include('site.educacao.partes.vazio', [
          'icone' => 'ph-graduation-cap',
          'titulo' => 'Em breve',
          'texto' => 'Ainda não há curso publicado por aqui. Assine para saber quando abrir a próxima turma.',
          'rotulo' => 'Quero receber novidades',
      ])
@endforelse

@foreach ($itens as $item)
    <dialog class="modal livro" id="curso-{{ $item->getKey() }}" aria-labelledby="curso-{{ $item->getKey() }}-titulo">
      <button class="modal__x" type="button" data-fecha aria-label="Fechar"><i class="ph ph-x" aria-hidden="true"></i></button>
      <div class="livro__corpo">
        <div class="livro__lado">
@if ($item->imagem)
          <img src="{{ $item->imagem->url() }}" alt="" width="{{ $item->imagem->width }}" height="{{ $item->imagem->height }}" loading="lazy" decoding="async">
@endif
@if (filled($item->external_url))
          <a class="btn btn--primary" href="{{ $item->external_url }}" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-arrow-up-right" aria-hidden="true"></i> Ver o curso</span></a>
@endif
        </div>
        <div class="livro__texto">
          <h3 class="t-h3" id="curso-{{ $item->getKey() }}-titulo">{{ $item->title }}</h3>
          @include('site.educacao.partes.meta', ['meta' => $dados($item)])
          @include('site.educacao.partes.texto', ['item' => $item])
        </div>
      </div>
    </dialog>
@endforeach

    @include('site.educacao.partes.mais', ['itens' => $itens])
  </div>
</section>

<!-- ===== cena final — newsletter ===== -->
<!-- textos: Escopo das Páginas/Cursos.md, seção 4 · destino: Painel/04 — Newsletter
     lista única de newsletter; o campo origem diz de qual página veio a inscrição -->
<section class="scene lvn scheme-04" id="avisos" data-scene data-tone="dark" aria-labelledby="avisos-titulo">
  <div class="scene__body">
    <div>
      <h2 class="t-h2 rise" id="avisos-titulo">Quer saber quando abre<br>a próxima turma?</h2>
      <p class="lvn__lead rise" data-d="1"><strong>As datas saem primeiro para quem está na lista</strong>,<br>junto com lançamentos e conteúdos novos.</p>
    </div>

    @include('site.educacao.partes.newsletter', [
        'chave' => 'newsletter-cursos',
        'origem' => 'cursos',
        'prefixo' => 'cs',
        'rotulo' => 'Quero receber novidades',
    ])
  </div>
</section>
@endsection

@section('pos-rodape')
@include('site.partials.barra')
@endsection
