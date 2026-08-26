@php
    $endereco = 'https://dramarinamariz.com.br/blog/'.$post->slug;
    // SEO de compartilhamento: seo_titulo ?? titulo · seo_descricao ?? descricao
    // · seo_imagem ?? imagem. A capa e o card continuam usando $post->imagem.
    $social = $post->seoImagem ?? $post->imagem ?? \App\Services\Site::configuracoes()->imagemDeCompartilhamento;

    // Dados estruturados do artigo, para o Google. Montados aqui dentro
    // porque `@context` e diretiva do Blade: escrito num `{{ }}` ele seria
    // compilado em vez de virar chave do JSON-LD.
    $jsonLd = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post->title,
        'description' => $post->descricaoSeo(),
        'image' => $social?->url() ?? '',
        'datePublished' => $post->published_at->format('Y-m-d'),
        'author' => ['@type' => 'Person', 'name' => $post->author],
        'publisher' => ['@type' => 'Organization', 'name' => 'Dra. Marina Mariz'],
        'mainEntityOfPage' => $endereco,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
@endphp
@extends('layouts.site', [
    'titulo' => $post->tituloSeo().' — Dra. Marina Mariz',
    'descricao' => $post->descricaoSeo(),
    'canonical' => $endereco,
    'ogTipo' => 'article',
    'ogImagem' => $social?->url() ?? '',
    'atual' => 'blog',
])

@section('jsonld')
<meta property="article:published_time" content="{{ $post->published_at->format('Y-m-d') }}">
<meta property="article:author" content="{{ $post->author }}">
@if ($post->categoria)
<meta property="article:section" content="{{ $post->categoria->name }}">
@endif
<script type="application/ld+json">
{{ str($jsonLd)->toHtmlString() }}
</script>
@endsection

@section('conteudo')
<!-- ===== capa do post ===== -->
<section class="scene pst scheme-04" data-scene data-tone="dark" aria-labelledby="post-titulo">
  @if ($post->imagem)
  <div class="pst__shot" aria-hidden="true"><img src="{{ $post->imagem->url() }}" alt="" width="{{ $post->imagem->width }}" height="{{ $post->imagem->height }}" fetchpriority="high"></div>
  @endif
  <div class="pst__veil" aria-hidden="true"></div>
  <div class="scene__body">
    <a class="pst__back rise" href="/blog"><i class="ph ph-arrow-left" aria-hidden="true"></i> Voltar ao blog</a>
    {{-- Some quando o post esta sem categoria. --}}
    @if ($post->categoria)
    <span class="pst__cat rise" data-d="1">{{ $post->categoria->name }}</span>
    @endif
    <h1 class="t-h1 rise" data-d="2" id="post-titulo">{{ $post->title }}</h1>
    <p class="pst__meta rise" data-d="3">
      <span>{{ $post->author }}</span>
      <span class="sep" aria-hidden="true">·</span>
      <time datetime="{{ $post->published_at->format('Y-m-d') }}">{{ $post->published_at->translatedFormat('j \d\e F \d\e Y') }}</time>
    </p>
  </div>
</section>

<!-- ===== corpo do artigo ===== -->
<section class="scene art scheme-05" data-scene data-tone="light" aria-label="Conteúdo do post">
  <div class="scene__body">
    <aside class="art__share" aria-label="Compartilhar">
      <span>Compartilhar</span>
      <a href="https://wa.me/?text={{ rawurlencode($post->title) }}" target="_blank" rel="noopener" aria-label="Compartilhar no WhatsApp"><i class="ph ph-whatsapp-logo" aria-hidden="true"></i></a>
      <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $endereco }}" target="_blank" rel="noopener" aria-label="Compartilhar no LinkedIn"><i class="ph ph-linkedin-logo" aria-hidden="true"></i></a>
      <button type="button" data-copy-link aria-label="Copiar link do post"><i class="ph ph-link-simple" aria-hidden="true"></i></button>
    </aside>

    <div>
      {{-- RichContentRenderer sanitizado, com as imagens resolvidas pela
           Biblioteca de Midia. Nunca conteudo cru. --}}
      <div class="art__body rise">{{ str($post->renderRichContent('content'))->toHtmlString() }}</div>

      <p class="art__note rise">Este conteúdo é informativo e não substitui consulta médica. <a href="/contato">Marque sua consulta agora</a>.</p>
    </div>

    {{-- O fecho e coluna fixa dentro do artigo; a variacao vem do painel.
         Sao quatro, fixas — o painel escolhe uma, nao escreve outra. --}}
    @switch($post->closing_cta)
    @case(\App\Enums\PostCta::Newsletter)
    <aside class="art__cta close--news rise rise--right" aria-labelledby="fecho-titulo">
      <div class="close__box" data-glow>
        <span class="close__ico"><i class="ph ph-envelope-simple" aria-hidden="true"></i></span>
        <div>
          <h2 class="t-h2" id="fecho-titulo">Quer conteúdo assim no seu e-mail?</h2>
          <p>Assine a newsletter e receba os melhores conteúdos sobre saúde materno-fetal toda semana.</p>
        </div>
        <form class="close__form" method="post" action="{{ route('formularios.newsletter') }}" novalidate>
          @csrf
          <input type="hidden" name="origem" value="blog">
          <input type="hidden" name="submission_id" value="{{ old('submission_id', \Illuminate\Support\Str::uuid()) }}">
          <input type="hidden" name="ciencia_politica" value="1">
          <input type="text" name="apelido" class="bln__trap" tabindex="-1" autocomplete="off" aria-hidden="true">
          <input type="text" name="nome" placeholder="Seu nome" autocomplete="name" required>
          <input type="email" name="email" placeholder="seu@email.com" autocomplete="email" required>
          <button class="btn btn--primary" type="submit"><span class="btn__label">Assinar <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></button>
        </form>
        <label class="close__consent">
          <input type="checkbox" name="aceita_newsletter" value="1" required>
          <span>Quero receber a newsletter e li a <a href="/politica-de-privacidade">Política de Privacidade</a></span>
        </label>
      </div>
    </aside>
    @break

    @case(\App\Enums\PostCta::Consulta)
    <aside class="art__cta rise rise--right" aria-labelledby="fecho-titulo">
      <div class="close__box" data-glow>
        <span class="close__ico"><i class="ph ph-calendar-check" aria-hidden="true"></i></span>
        <div>
          <h2 class="t-h2" id="fecho-titulo">Quer conversar sobre o seu caso?</h2>
          <p>Cada gestação tem a sua história. Agende uma consulta e receba orientação feita para a sua.</p>
        </div>
        <a class="btn btn--primary" href="/contato"><span class="btn__label">Agendar consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
      </div>
    </aside>
    @break

    @case(\App\Enums\PostCta::Material)
    <aside class="art__cta rise rise--right" aria-labelledby="fecho-titulo">
      <div class="close__box" data-glow>
        <span class="close__ico"><i class="ph ph-download-simple" aria-hidden="true"></i></span>
        <div>
          <h2 class="t-h2" id="fecho-titulo">Baixe o material completo</h2>
          {{-- Texto escrito no painel, obrigatorio neste CTA. --}}
          <p>{{ $post->material_description }}</p>
        </div>
        {{-- Arquivo anexado ao post: o botao entrega o arquivo direto, sem
             modal e sem gerar lead (doc 02). --}}
        @if ($post->arquivo)
        <a class="btn btn--primary" href="{{ \App\Http\Controllers\DownloadProtegidoController::linkPara($post->arquivo) }}" download><span class="btn__label">Baixar material gratuito <i class="ph ph-arrow-down" aria-hidden="true"></i></span></a>
        @endif
      </div>
    </aside>
    @break

    @case(\App\Enums\PostCta::Podcast)
    <aside class="art__cta rise rise--right" aria-labelledby="fecho-titulo">
      <div class="close__box" data-glow>
        <span class="close__ico"><i class="ph ph-microphone-stage" aria-hidden="true"></i></span>
        <div>
          {{-- Texto fixo: o painel nao edita nada aqui, so o endereco do botao. --}}
          <h2 class="t-h2" id="fecho-titulo">Esse assunto virou episódio</h2>
          <p>A conversa completa está no Sem Neura Podcast, com a Dra. Marina Mariz e a Dra. Carol Flores.</p>
        </div>
        <a class="btn btn--primary" href="{{ $post->episode_url }}" target="_blank" rel="noopener"><span class="btn__label">Ouvir o episódio <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
      </div>
    </aside>
    @break
    @endswitch
  </div>
</section>

{{-- Ate 4 relacionados; arquivado, rascunho ou lixeira nem entra na consulta. --}}
@if ($relacionados->isNotEmpty())
<!-- ===== relacionados ===== -->
<section class="scene rel scheme-05" data-scene data-tone="light" aria-labelledby="rel-titulo">
  <div class="scene__body">
    <header class="sec__head sec__head--stack">
      <h2 class="t-h2 rise" id="rel-titulo">Continue lendo</h2>
    </header>
    <ol class="rel__grid">
      @foreach ($relacionados as $relacionado)
      @include('site.blog.cartao', ['post' => $relacionado])
      @endforeach
    </ol>

    <div class="rel__acoes rise" data-d="2">
      <a class="btn btn--outline" href="/blog"><span class="btn__label"><i class="ph ph-arrow-left" aria-hidden="true"></i> Voltar para o blog</span></a>
      <a class="btn btn--primary" href="/contato"><span class="btn__label">Marcar uma consulta agora <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
    </div>
  </div>
</section>
@endif
@endsection
