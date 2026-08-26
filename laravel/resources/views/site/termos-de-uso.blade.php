@extends('layouts.site', [
    'titulo' => 'Termos de Uso — Dra. Marina Mariz',
    'descricao' => 'Termos de Uso do site da Dra. Marina Mariz: finalidade do conteúdo, propriedade intelectual, materiais gratuitos, produtos pagos e limites de responsabilidade.',
    'canonical' => 'https://dramarinamariz.com.br/termos-de-uso',
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/marina-retrato.webp',
])

@section('robots')
<meta name="robots" content="index,follow">
@endsection

@section('conteudo')
<!-- ===== cena 1 — capa do documento ===== -->
<!-- textos: Escopo das Páginas/Termos de Uso.md · dados de contato: Painel/03 — Contatos e Dados
     ⚠ texto-modelo: revisão de advogado antes de ir ao ar -->
<section class="scene legh scheme-04" id="documento" data-scene data-tone="dark" aria-labelledby="doc-titulo">
  <div class="legh__halo" aria-hidden="true"></div>
  <div class="scene__body">
    <nav class="crumbs rise" aria-label="Trilha">
      <a href="/">Início</a>
      <i class="ph ph-caret-right" aria-hidden="true"></i>
      <span aria-current="page">{{ $documento['titulo'] ?? 'Termos de Uso' }}</span>
    </nav>
    <h1 class="t-h1 rise" data-d="1" data-split id="doc-titulo">{{ $documento['titulo'] ?? 'Termos de Uso' }}</h1>
    <p class="legh__lead rise" data-d="2">O que este site é, o que ele não é e as regras de quem navega, baixa um material ou preenche um formulário por aqui.</p>
    <div class="legh__meta rise" data-d="3">
@if (! empty($documento['atualizado_em']))
      <span class="legh__data"><i class="ph ph-calendar-check" aria-hidden="true"></i>Última atualização: <time datetime="{{ $documento['atualizado_em']->format('Y-m-d') }}">{{ $documento['atualizado_em']->translatedFormat('j \d\e F \d\e Y') }}</time></span>
@endif
      <span class="legh__tempo"><i class="ph ph-clock" aria-hidden="true"></i>{{ $documento['minutos'] ?? 5 }} min de leitura</span>
    </div>
  </div>
</section>

<!-- ===== cena 2 — o documento, com índice fixo à esquerda ===== -->
<section class="scene leg scheme-05" data-scene data-tone="light" aria-label="Termos de Uso">
  <div class="scene__body">
    <nav class="leg__indice" aria-label="Índice do documento">
      <span class="leg__indice-tit">Nesta página</span>
      <ol class="leg__toc">
@foreach ($documento['indice'] ?? [] as $secao)
        <li><a href="#{{ $secao['id'] }}">{{ $secao['titulo'] }}</a></li>
@endforeach
      </ol>
      <a class="leg__par" href="/politica-de-privacidade"><i class="ph ph-arrow-right" aria-hidden="true"></i>Ler também a Política de Privacidade</a>
    </nav>

    {{-- Texto publicado no painel, renderizado pelo sanitizador do
         Filament. Rascunho nao aparece aqui. --}}
    <article class="leg__texto art__body rise">{{ str($documento['conteudo'] ?? '')->toHtmlString() }}</article>
  </div>
</section>

<!-- ===== cena 3 — dúvidas ===== -->
<section class="scene legc scheme-04" id="duvidas" data-scene data-tone="dark" aria-labelledby="duvidas-titulo">
  <div class="scene__body">
    <div>
      <h2 class="t-h2 rise" id="duvidas-titulo">Ficou alguma dúvida<br>sobre este documento?</h2>
      <p class="legc__lead rise" data-d="1">Para autorização de uso de conteúdo ou qualquer dúvida sobre estes Termos, fale com a equipe.</p>
    </div>
    <div class="legc__acoes rise rise--zoom" data-d="2">
      <a class="btn btn--primary" href="mailto:{{ $email }}"><span class="btn__label"><i class="ph ph-envelope-simple" aria-hidden="true"></i> {{ $email }}</span></a>
      <a class="btn btn--outline" href="/contato"><span class="btn__label">Falar com a equipe <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a>
    </div>
  </div>
</section>
@endsection

@section('pos-rodape')
<!-- documento legal não recebe a barra fixa de agendamento -->
<div class="lerbar" aria-hidden="true"></div>
@endsection
