{{--
    Página 404 (vault: Escopo das Páginas/Página 404.md). Não existe HTML de
    origem dela em `site/`, então a cena é montada só com o vocabulário que já
    está no CSS: a cena centrada `.ctw`, o selo de acento, `.crumbs` para os
    atalhos e a nota discreta. Nenhuma regra nova de estilo.
--}}
@extends('layouts.site', [
    'titulo' => 'Página não encontrada — Dra. Marina Mariz',
    'descricao' => 'A página que você procurou não existe mais. O caminho de volta é curto.',
    'canonical' => 'https://dramarinamariz.com.br/',
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/marina-retrato.webp',
])

@section('robots')
<meta name="robots" content="noindex,follow">
@endsection

@section('conteudo')
<section class="scene ctw scheme-04" id="nao-encontrada" data-scene data-tone="dark" aria-labelledby="e404-titulo">
  <div class="scene__body">
    <p class="t-h1 rise rise--zoom" style="color:var(--accent-text)" aria-hidden="true">404</p>
    <h1 class="t-h2 rise" data-d="1" id="e404-titulo">Essa página não existe mais.</h1>
    <p class="ctw__lead rise" data-d="2">Pode ter mudado de endereço, ou o link que te trouxe até aqui está incompleto. Não tem problema — o caminho de volta é curto.</p>
    <a class="btn btn--primary rise rise--zoom" data-d="3" href="/"><span class="btn__label">Voltar para o início <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a>
    <nav class="crumbs rise" data-d="4" aria-label="Atalhos">
      <a href="/sobre">Sobre</a>
      <i class="ph ph-dot-outline" aria-hidden="true"></i>
      <a href="/especialidades">Especialidades</a>
      <i class="ph ph-dot-outline" aria-hidden="true"></i>
      <a href="/educacao">Educação</a>
      <i class="ph ph-dot-outline" aria-hidden="true"></i>
      <a href="/blog">Blog</a>
      <i class="ph ph-dot-outline" aria-hidden="true"></i>
      <a href="/contato">Contato</a>
    </nav>
    <p class="ctw__nota rise" data-d="5">Precisa falar com a gente agora? <a href="{{ \App\Services\Site::linkWhatsapp() }}" target="_blank" rel="noopener">Chamar no WhatsApp</a></p>
  </div>
</section>
@endsection
