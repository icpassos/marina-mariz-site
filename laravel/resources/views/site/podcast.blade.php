@extends('layouts.site', [
    'titulo' => 'Sem Neura Podcast — Dra. Marina Mariz',
    'descricao' => 'Sem Neura Podcast: Dra. Marina Mariz e Dra. Carol Flores conversam sobre gestação, parto, puerpério e maternidade com ciência, clareza e acolhimento.',
    'canonical' => 'https://dramarinamariz.com.br/podcast',
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/podcast-estudio.webp',
    'atual' => 'podcast',
    'classeNav' => 'nav nav--pod pod-noite',
    'classeDrawer' => 'drawer drawer--pod pod-noite',
    'classeRodape' => 'footer pod-noite',
])

@section('preloads')
<link rel="preload" href="/assets/fonts/retro-star-400.woff2" as="font" type="font/woff2" crossorigin>
@endsection

@section('conteudo')
<!-- ===== cena 1 — abertura ===== -->
<!-- textos: Escopo das Páginas/Podcast Sem Neura.md, seção 1 -->
<section class="scene pod pod-noite pdb pod-luz" id="fases" data-glow data-scene data-tone="dark" aria-labelledby="fases-titulo">
  <div class="scene__body">
    <img class="pdb__logo rise" src="assets/logo/podcast-sem-neura-noite.svg" alt="Sem Neura Podcast" width="172" height="64">
    <h1 class="pdb__claim rise" data-d="1" id="fases-titulo" aria-label="Chega de neura na gestação e na maternidade"><span class="pdb__ghost" aria-hidden="true">Chega de neura na maternidade</span><span class="intro__type" data-type='["Chega de neura na gestação","Chega de neura na maternidade"]' aria-hidden="true"></span></h1>
  </div>
</section>

<!-- ===== cena 2 — herói ===== -->
<!-- textos: Escopo das Páginas/Podcast Sem Neura.md, seção 2 -->
<section class="scene pod pod-lima pdh" id="podcast" data-scene data-tone="light" aria-labelledby="pod-titulo">
  <div class="scene__body">
    <div class="pdh__copy">
      <ul class="pod__names rise">
        <li>Marina Mariz</li>
        <li class="pod__wave"><span class="wave" aria-hidden="true"><span style="animation-delay:-0.00s"></span><span style="animation-delay:-0.09s"></span><span style="animation-delay:-0.18s"></span><span style="animation-delay:-0.27s"></span><span style="animation-delay:-0.36s"></span><span style="animation-delay:-0.45s"></span><span style="animation-delay:-0.54s"></span><span style="animation-delay:-0.63s"></span><span style="animation-delay:-0.72s"></span><span style="animation-delay:-0.81s"></span><span style="animation-delay:-0.90s"></span><span style="animation-delay:-0.99s"></span><span style="animation-delay:-1.08s"></span><span style="animation-delay:-1.17s"></span><span style="animation-delay:-1.26s"></span><span style="animation-delay:-1.35s"></span><span style="animation-delay:-1.44s"></span><span style="animation-delay:-1.53s"></span></span></li>
        <li>Carol Flores</li>
      </ul>
      <h2 class="t-h1 rise" data-d="1" data-split id="pod-titulo">Uma conversa honesta<br>sobre gestar e maternar</h2>
      <p class="rise" data-d="2">Nem tudo é medo e culpa. Nem tudo é alegria e encanto.</p>
      <p class="rise" data-d="3">Maternar é viver os dois extremos, e tudo o que existe entre eles.</p>
      <p class="rise" data-d="3">Um espaço para falar sobre <strong>escolhas, medos, expectativas, mudanças e <br class="br-desk">descobertas</strong>, da tentativa de engravidar ao puerpério.</p>
      <p class="rise" data-d="3">Duas obstetras, duas mães, convidados, especialistas e muitas conversas sobre <br class="br-desk">maternidade com <strong>ciência</strong>, <strong>clareza</strong>, <strong>acolhimento</strong> e, claro, <strong>sem neura</strong>.</p>
      <div class="pdh__links rise" data-d="4">
        <span class="pdh__cue">Ouça agora:</span>
        <a class="btn btn--primary" href="https://www.instagram.com/semneurapodcast" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-instagram-logo" aria-hidden="true"></i> @semneurapodcast</span></a>
        <a class="btn btn--outline" href="https://www.youtube.com/@SemNeuraPodcast" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-youtube-logo" aria-hidden="true"></i> YouTube</span></a>
      </div>
    </div>

    <figure class="pdh__booth rise rise--right" data-d="2">
      <div class="pdh__reel">
        <img src="assets/img/podcast-estudio.webp" alt="Dra. Marina Mariz na cabine do Sem Neura Podcast" width="571" height="627" fetchpriority="high">
        <img src="assets/img/podcast-estudio-2.webp" alt="" width="868" height="972" loading="lazy" decoding="async">
      </div>
      <a class="pdh__live" href="https://www.youtube.com/@SemNeuraPodcast" target="_blank" rel="noopener">Ouça agora</a>
      <div class="wave" aria-hidden="true"><span style="animation-delay:0.00s"></span><span style="animation-delay:-0.09s"></span><span style="animation-delay:-0.18s"></span><span style="animation-delay:-0.27s"></span><span style="animation-delay:-0.36s"></span><span style="animation-delay:-0.45s"></span><span style="animation-delay:-0.54s"></span><span style="animation-delay:-0.63s"></span><span style="animation-delay:-0.72s"></span><span style="animation-delay:-0.81s"></span><span style="animation-delay:-0.90s"></span><span style="animation-delay:-0.99s"></span><span style="animation-delay:-1.08s"></span><span style="animation-delay:-1.17s"></span><span style="animation-delay:-1.26s"></span><span style="animation-delay:-1.35s"></span><span style="animation-delay:-1.44s"></span><span style="animation-delay:-1.53s"></span><span style="animation-delay:-1.62s"></span><span style="animation-delay:-1.71s"></span><span style="animation-delay:-1.80s"></span><span style="animation-delay:-1.89s"></span><span style="animation-delay:-1.98s"></span><span style="animation-delay:-2.07s"></span></div>
    </figure>

    <div class="pdh__fases">
      <p class="pdb__claim rise"><span>A gente explica tudo. Com ciência.</span><span>Com clareza. Com segurança. E… <b>sem neura.</b></span></p>
    </div>
  </div>
</section>

<!-- ===== cena 3 — sobre o podcast ===== -->
<!-- textos: Escopo das Páginas/Podcast Sem Neura.md, seção 5 -->
<section class="scene pod pod-noite pds pod-luz" id="sobre" data-glow data-scene data-tone="dark" aria-labelledby="sobre-titulo">
  <div class="scene__body">
    <div class="pds__grid">
      <h2 class="t-h2 rise" id="sobre-titulo">O que é o<br>Sem Neura<br>Podcast</h2>
      <div class="pds__text">
        <p class="rise" data-d="1">Um espaço para falar sobre a <strong>maternidade como ela realmente é</strong>: com dúvidas, escolhas, mudanças, alegrias e tudo o que existe entre uma fase e outra.</p>
        <p class="rise" data-d="2">Apresentado pela Dra. Marina Mariz e pela Dra. Carol Flores (ginecologistas, obstetras e mães), o Sem Neura reúne <strong>ciência, experiência e conversas honestas</strong> sobre fertilidade, gestação, parto, puerpério, amamentação e saúde da mulher. Aqui, informação de qualidade não vem para dizer como você deve viver essa experiência. Vem para ajudar você a <strong>entender possibilidades, fazer escolhas</strong> e atravessar cada etapa com mais <strong>clareza e segurança</strong>.</p>
      </div>
    </div>

    <h3 class="pds__tag pds__tag--topo rise" data-d="3">Em que fase você está?</h3>
    <ol class="pds__fases rise" data-d="4">
      <li class="pds__fase"><i class="ph ph-seal-check" aria-hidden="true"></i><strong>Tentando engravidar</strong></li>
      <li class="pds__fase"><i class="ph ph-baby" aria-hidden="true"></i><strong>Primeira gestação</strong></li>
      <li class="pds__fase"><i class="ph ph-users" aria-hidden="true"></i><strong>Segunda gestação ou mais</strong></li>
      <li class="pds__fase"><i class="ph ph-clock-countdown" aria-hidden="true"></i><strong>Me preparando para o parto</strong></li>
      <li class="pds__fase"><i class="ph ph-hand-heart" aria-hidden="true"></i><strong>Vivendo o pós-parto</strong></li>
    </ol>

    <p class="pds__tag rise" data-d="5">Estamos com você em todas as fases.</p>
  </div>
</section>

<!-- ===== cena 4 — chamada final ===== -->
<!-- textos: Escopo das Páginas/Podcast Sem Neura.md, seção 7 -->
<section class="scene pod pod-lima pdc" id="seguir" data-scene data-tone="light" aria-labelledby="cta-podcast">
  <div class="scene__body">
    <img class="pdc__logo rise rise--zoom" src="assets/logo/podcast-sem-neura.svg" alt="Sem Neura Podcast" width="172" height="64" id="cta-podcast" loading="lazy">
    <div class="pdc__links rise rise--zoom" data-d="2">
      <span class="pdh__cue">Ouça agora:</span>
      <a class="btn btn--primary" href="https://www.instagram.com/semneurapodcast" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-instagram-logo" aria-hidden="true"></i> Instagram · @semneurapodcast</span></a>
      <a class="btn btn--outline" href="https://www.youtube.com/@SemNeuraPodcast" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-youtube-logo" aria-hidden="true"></i> YouTube · @SemNeuraPodcast</span></a>
    </div>
  </div>
</section>
@endsection
