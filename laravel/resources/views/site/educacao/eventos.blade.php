@extends('layouts.site', [
    'titulo' => 'Eventos — Dra. Marina Mariz',
    'descricao' => 'Eventos da Dra. Marina Mariz: imersões, rodas de conversa e palestras, presenciais e online, sobre gestação e maternidade.',
    'canonical' => 'https://dramarinamariz.com.br/educacao/eventos',
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/eventos-roda.webp',
])

@php
    use Illuminate\Support\HtmlString;

    // "19h" e "21h30": hora cheia nao carrega minuto.
    $hora = fn ($data) => $data->format('G').'h'.($data->format('i') === '00' ? '' : $data->format('i'));

    $quando = fn ($item) => new HtmlString(
        '<time datetime="'.e($item->starts_at->toIso8601String()).'">'.e($item->starts_at->format('d/m').', '.$hora($item->starts_at)).'</time>'
        .($item->ends_at ? ' às <time datetime="'.e($item->ends_at->toIso8601String()).'">'.e($hora($item->ends_at)).'</time>' : '')
    );

    // Local so nos presenciais (doc 01); nos online o formato ja diz tudo.
    $onde = fn ($item) => $item->event_format === \App\Enums\EventFormat::Presencial
        ? implode(' · ', array_filter([$item->venue_city, $item->venue_state]))
        : null;

    $dados = fn ($item) => [$quando($item), $item->event_format?->getLabel(), $onde($item)];
@endphp

@section('conteudo')
<!-- ===== cena 1 — herói ===== -->
<!-- textos: Escopo das Páginas/Eventos.md, seção 1 -->
<section class="scene lvh scheme-04" id="eventos" data-scene data-tone="dark" aria-labelledby="eventos-titulo">
  <div class="lvh__halo" aria-hidden="true"></div>
  <div class="lvh__grain" aria-hidden="true"></div>
  <div class="scene__body">
    <div>
      <nav class="crumbs rise" aria-label="Trilha">
        <a href="/educacao">Educação</a>
        <i class="ph ph-caret-right" aria-hidden="true"></i>
        <span aria-current="page">Eventos</span>
      </nav>
      <h1 class="t-h1 rise" data-d="1" data-split id="eventos-titulo">Encontros<br>que conectam</h1>
      <p class="lvh__lead rise" data-d="2"><strong>Imersões, rodas de conversa e palestras</strong> — presenciais<br>e online — com profundidade e acolhimento.</p>
      <p class="lvh__lead rise" data-d="2"><strong>Grupos pequenos</strong>, <strong>tempo de escuta</strong> e espaço <strong>para<br>perguntar</strong> o que não cabe numa consulta de rotina.</p>
      <div class="lvh__actions rise" data-d="3">
        <a class="btn btn--primary" href="#lista"><span class="btn__label">Ver os próximos <i class="ph ph-arrow-down" aria-hidden="true"></i></span></a>
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
      <figure><img src="/assets/img/eventos-maes.webp" alt="" width="1080" height="720" loading="lazy" decoding="async"></figure>
      <figure><img src="/assets/img/eventos-imersao.webp" alt="" width="1080" height="1620" loading="lazy" decoding="async"></figure>
      <figure><img src="/assets/img/eventos-aula.webp" alt="" width="1080" height="608" loading="lazy" decoding="async"></figure>
@endforelse
    </div>
  </div>
</section>

<!-- ===== cena 2 — lista ===== -->
<!-- textos: Escopo das Páginas/Eventos.md, seção 2 · itens: Painel/01 — Catálogo Educação -->
<section class="scene lvl scheme-05" id="lista" data-scene data-tone="light" aria-label="Próximos eventos">
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
            <a class="btn btn--primary book__buy" href="{{ $item->external_url }}" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-ticket" aria-hidden="true"></i> Garantir vaga</span></a>
@endif
            <button class="btn btn--outline book__mais" type="button" data-abre="evento-{{ $item->getKey() }}"><span class="btn__label">Saber mais</span></button>
          </div>
        </article></li>
@if ($loop->last)
      </ol>
@endif
@empty
      @include('site.educacao.partes.vazio', [
          'icone' => 'ph-calendar-blank',
          'titulo' => 'Em breve',
          'texto' => 'Ainda não há evento marcado por aqui. Assine para saber assim que a próxima data sair.',
          'rotulo' => 'Receber avisos de eventos',
      ])
@endforelse

@foreach ($itens as $item)
    <dialog class="modal livro" id="evento-{{ $item->getKey() }}" aria-labelledby="evento-{{ $item->getKey() }}-titulo">
      <button class="modal__x" type="button" data-fecha aria-label="Fechar"><i class="ph ph-x" aria-hidden="true"></i></button>
      <div class="livro__corpo">
        <div class="livro__lado">
@if ($item->imagem)
          <img src="{{ $item->imagem->url() }}" alt="" width="{{ $item->imagem->width }}" height="{{ $item->imagem->height }}" loading="lazy" decoding="async">
@endif
@if (filled($item->external_url))
          <a class="btn btn--primary" href="{{ $item->external_url }}" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-ticket" aria-hidden="true"></i> Garantir vaga</span></a>
@endif
        </div>
        <div class="livro__texto">
          <h3 class="t-h3" id="evento-{{ $item->getKey() }}-titulo">{{ $item->title }}</h3>
          @include('site.educacao.partes.meta', ['meta' => $dados($item)])
          @include('site.educacao.partes.texto', ['item' => $item])
        </div>
      </div>
    </dialog>
@endforeach

    @include('site.educacao.partes.mais', ['itens' => $itens])
  </div>
</section>

@if ($passados->isNotEmpty())
<!-- ===== cena 3 — já aconteceram ===== -->
<!-- textos: Escopo das Páginas/Eventos.md, seção 3
     o site move o evento de uma seção para a outra sozinho quando a data de fim passa -->
<section class="scene lvl lvl--passados scheme-05" id="passados" data-scene data-tone="light" aria-labelledby="passados-titulo">
  <div class="scene__body">
    <h2 class="t-h2 rise" id="passados-titulo">Já aconteceram</h2>
    <ol class="lvl__grid">
@foreach ($passados as $item)
        <li class="rise" data-d="1"><article class="book card book--passado">
          <figure class="book__shot">
@if ($item->imagem)
            <img src="{{ $item->imagem->url() }}" alt="" width="{{ $item->imagem->width }}" height="{{ $item->imagem->height }}" loading="lazy" decoding="async">
@endif
          </figure>
          <h3 class="book__title">{{ $item->title }}</h3>
          @include('site.educacao.partes.meta', ['meta' => [
              new HtmlString('<time datetime="'.e($item->starts_at->format('Y-m-d')).'">'.e($item->starts_at->format('d/m/Y')).'</time>'),
              $onde($item) ?: $item->event_format?->getLabel(),
          ]])
        </article></li>
@endforeach
    </ol>
  </div>
</section>
@endif

<!-- ===== cena 4 — avisos de eventos ===== -->
<!-- textos: Escopo das Páginas/Eventos.md, seção 5 · destino: Painel/04 — Newsletter -->
<section class="scene lvn lvn--eventos scheme-04" id="avisos" data-scene data-tone="dark" aria-labelledby="avisos-titulo">
  <div class="scene__body">
    <!-- textos: Escopo das Páginas/Eventos.md, seção 4 — convite para palestras -->
    <div class="lvn__card rise">
      <span class="lvn__card-ico"><i class="ph ph-microphone-stage" aria-hidden="true"></i></span>
      <div>
        <h3>Quer levar a Dra. Marina ao seu evento?</h3>
        <p>Palestras, aulas e participações em congressos, formações e encontros de equipe.</p>
      </div>
      <a class="btn btn--outline" href="/contato?assunto=palestras"><span class="btn__label">Fazer um convite <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
    </div>

    <div class="lvn__col">
      <h2 class="t-h2 rise" data-d="1" id="avisos-titulo">Fique sabendo dos próximos</h2>
      <p class="lvn__lead rise" data-d="1">As datas saem primeiro para quem está na lista — e as vagas presenciais são poucas.</p>

      @include('site.educacao.partes.newsletter', [
          'chave' => 'newsletter-eventos',
          'origem' => 'eventos',
          'prefixo' => 'ev',
          'rotulo' => 'Receber avisos de eventos',
      ])
    </div>
  </div>
</section>
@endsection

@section('pos-rodape')
@include('site.partials.barra')
@endsection
