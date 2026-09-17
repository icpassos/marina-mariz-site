@extends('layouts.site', [
    'titulo' => 'Blog — Dra. Marina Mariz',
    'descricao' => 'Artigos, episódios e materiais educativos da Dra. Marina Mariz sobre gestação, parto, puerpério e saúde da mulher.',
    'canonical' => $canonical,
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/marina-sobre.webp',
    'atual' => 'blog',
])

@section('conteudo')
<!-- ===== cena 1 — herói e busca ===== -->
<!-- textos: Escopo das Páginas/Blog.md, seção 1 -->
<section class="scene blh scheme-04" id="blog" data-scene data-tone="dark" aria-labelledby="blog-titulo">
  <div class="blh__halo" aria-hidden="true"></div>
  <div class="blh__grain" aria-hidden="true"></div>
  <div class="scene__body">
    <h1 class="t-h1 rise" data-d="1" data-split id="blog-titulo"><span class="blh__line">Informação que orienta.</span><span class="blh__line">Conhecimento que dá segurança.</span></h1>
    <p class="blh__lead rise" data-d="2">Artigos e episódios de podcast produzidos pela Dra. Marina Mariz para ajudar você a viver a maternidade com mais <strong>clareza, segurança e autonomia</strong>.</p>

    {{-- Busca por querystring: /blog?q=... — o termo volta no campo para refinar
         em vez de redigitar. Nada disso e gravado em lugar nenhum (doc 02). --}}
    <form class="blh__search rise" data-d="3" method="get" action="/blog" role="search">
      <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
      <label class="sr-only" for="q">Buscar no blog</label>
      <input id="q" name="q" type="search" placeholder="Buscar por assunto, título ou palavra-chave" value="{{ $termo }}">
      <button class="btn btn--primary" type="submit"><span class="btn__label">Buscar</span></button>
    </form>
  </div>
</section>

<!-- ===== cena 2 — abas, lista e paginação ===== -->
<!-- textos: Escopo das Páginas/Blog.md, seção 2 -->
<section class="scene bll scheme-05" id="lista" data-scene data-tone="light" aria-label="Publicações">
  <div class="scene__body">

    {{-- As abas NAO sao fixas no codigo: sao as categorias com post publicado,
         na ordem do painel. Categoria vazia nao vira aba. --}}
    <div class="bll__bar">
      <ul class="bll__tabs">
        <li><a class="bll__tab" href="/blog"@if (! $categoriaAtual) aria-current="page"@endif>Todos <b>{{ $totalNoAr }}</b></a></li>
        @foreach ($categorias as $categoria)
        <li><a class="bll__tab" href="/blog?categoria={{ $categoria->slug }}"@if ($categoriaAtual?->is($categoria)) aria-current="page"@endif>{{ $categoria->name }} <b>{{ $categoria->posts_no_ar_count }}</b></a></li>
        @endforeach
      </ul>
      <span class="bll__count">{{ $posts->total() }} de {{ $totalNoAr }} posts</span>
    </div>

    @if ($posts->isNotEmpty())
    <ol class="bll__grid">
      @foreach ($posts as $post)
      @include('site.blog.cartao', ['post' => $post])
      @endforeach
    </ol>
    @else
    {{-- Sem resultado: o termo repetido, dois caminhos de saida e os atalhos
         de categoria — que sao as mesmas categorias das abas, vindas do painel.
         Nao existe lista de termos escrita no codigo. --}}
    <div class="bll__empty">
      <span class="bll__empty-ico"><i class="ph ph-file-dashed" aria-hidden="true"></i></span>
      <h3 class="t-h3">Nenhum post encontrado</h3>
      @if (filled($termo))
      <p>Não achamos nada para <b>&ldquo;{{ $termo }}&rdquo;</b>. Tente uma palavra mais curta, ou veja tudo que já foi publicado.</p>
      @else
      <p>Nenhum post encontrado para essa busca.</p>
      @endif
      <div class="bll__empty-acts">
        <a class="btn btn--primary" href="/blog"><span class="btn__label">Ver todos os posts <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a>
        <a class="btn btn--outline" href="/contato"><span class="btn__label">Perguntar direto <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
      </div>
      @if ($categorias->isNotEmpty())
      <p class="bll__empty-hint">Ver por categoria:
        @foreach ($categorias as $categoria)
        <a href="/blog?categoria={{ $categoria->slug }}">{{ $categoria->name }}</a>
        @endforeach
      </p>
      @endif
    </div>
    @endif

    {{ $posts->links('site.blog.paginacao') }}
  </div>
</section>

<!-- ===== cena 3 — newsletter ===== -->
<!-- textos: Escopo das Páginas/Blog.md, seção 3 · destino: Painel/04 — Leads -->
<section class="scene bln scheme-04" id="newsletter" data-scene data-tone="dark" aria-labelledby="news-titulo">
  <div class="scene__body">
    <div>
      <h2 class="t-h2 rise" id="news-titulo">Quer conteúdo diretamente<br>no seu e-mail?</h2>
      <p class="bln__lead rise" data-d="1">Assine a newsletter e receba os melhores<br>conteúdos sobre <strong>saúde materno-fetal</strong> toda semana.</p>
    </div>

    <form class="bln__form rise rise--right" data-d="2" method="post" action="{{ route('formularios.newsletter') }}" novalidate>
      @csrf
      <input type="hidden" name="origem" value="blog">
          <input type="hidden" name="submission_id" value="{{ old('submission_id', \Illuminate\Support\Str::uuid()) }}">
          <input type="hidden" name="ciencia_politica" value="1">
      <input type="text" name="apelido" class="bln__trap" tabindex="-1" autocomplete="off" aria-hidden="true">
      <div class="bln__row">
        <p class="bln__field"><label for="nl-nome">Nome</label><input id="nl-nome" name="nome" type="text" placeholder="Como podemos te chamar" autocomplete="name" required></p>
        <p class="bln__field"><label for="nl-email">E-mail</label><input id="nl-email" name="email" type="email" placeholder="seu@email.com" autocomplete="email" required></p>
      </div>
      <label class="bln__consent">
        <input type="checkbox" name="aceita_newsletter" value="1" required>
        <span>Quero receber novidades por e-mail e li a <a href="/politica-de-privacidade">Política de Privacidade</a></span>
      </label>
      <button class="btn btn--primary" type="submit"><span class="btn__label">Assinar newsletter <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></button>
    </form>
  </div>
</section>
@endsection

@section('pos-rodape')
@include('site.partials.barra')
@endsection
