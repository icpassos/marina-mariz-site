@extends('layouts.site', [
    'titulo' => 'Educação — Dra. Marina Mariz',
    'descricao' => 'Livros, e-books, cursos, eventos, materiais gratuitos e formação profissional da Dra. Marina Mariz — conhecimento sobre gestar e maternar em um só lugar.',
    'canonical' => 'https://dramarinamariz.com.br/educacao',
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/marina-sobre.webp',
])

@section('conteudo')
<!-- ===== cena 1 — herói ===== -->
<!-- textos: Escopo das Páginas/Educação.md, seção 1 -->
<section class="scene eduh scheme-04" id="educacao" data-scene data-tone="dark" aria-labelledby="educacao-titulo">
  <div class="eduh__halo" aria-hidden="true"></div>
  <div class="eduh__grain" aria-hidden="true"></div>
  <div class="scene__body">
    <span class="eyebrow rise">Educação</span>
    <h1 class="t-h1 rise" data-d="1" data-split id="educacao-titulo">Conhecimento<br>que transforma<br>a forma de gestar</h1>
    <p class="eduh__lead rise" data-d="2">Compartilhar conhecimento com <strong>generosidade</strong>, ampliando o<br>acesso a um novo padrão de excelência no cuidado materno-fetal.<br>Para gestantes, famílias e profissionais.</p>
    <div class="eduh__marcas rise" data-d="3">
      <span><b>6</b> frentes</span>
      <span><b>1</b> propósito</span>
      <span><b>0</b> pegadinha nos materiais gratuitos</span>
    </div>
  </div>
</section>

<!-- ===== cena 2 — as seis frentes ===== -->
<!-- textos: Escopo das Páginas/Educação.md, seção 2 · o hub não tem conteúdo próprio, só distribui -->
<section class="scene edug scheme-05" id="frentes" data-scene data-tone="light" aria-label="Frentes de Educação">
  <div class="scene__body">
    <ol class="hub__grid">
      <li class="rise" data-d="1"><a class="hub__card" href="/educacao/livros" data-glow>
        <span class="hub__ico"><i class="ph ph-book-open" aria-hidden="true"></i></span>
        <span class="hub__tag">Publicações</span>
        <h3 class="hub__titulo">Livros</h3>
        <p class="hub__desc">Publicações autorais que traduzem anos de prática clínica em conhecimento acessível. Obras que acompanham gestantes e profissionais em cada fase da jornada.</p>
        <span class="hub__go">Explorar <i class="ph ph-arrow-right" aria-hidden="true"></i></span>
      </a></li>
      <li class="rise" data-d="2"><a class="hub__card" href="/educacao/ebooks" data-glow>
        <span class="hub__ico"><i class="ph ph-file-text" aria-hidden="true"></i></span>
        <span class="hub__tag">Digital</span>
        <h3 class="hub__titulo">E-books</h3>
        <p class="hub__desc">Conteúdo digital exclusivo, desenvolvido para quem busca informação de qualidade sem sair de casa. Guias práticos, aprofundados e baseados em evidência.</p>
        <span class="hub__go">Explorar <i class="ph ph-arrow-right" aria-hidden="true"></i></span>
      </a></li>
      <li class="rise" data-d="3"><a class="hub__card" href="/educacao/cursos" data-glow>
        <span class="hub__ico"><i class="ph ph-graduation-cap" aria-hidden="true"></i></span>
        <span class="hub__tag">Formação</span>
        <h3 class="hub__titulo">Cursos</h3>
        <p class="hub__desc">Formações estruturadas para gestantes e casais que desejam viver a gestação com protagonismo. Conhecimento que transforma insegurança em preparo real.</p>
        <span class="hub__go">Explorar <i class="ph ph-arrow-right" aria-hidden="true"></i></span>
      </a></li>
      <li class="rise" data-d="3"><a class="hub__card" href="/educacao/eventos" data-glow>
        <span class="hub__ico"><i class="ph ph-calendar-blank" aria-hidden="true"></i></span>
        <span class="hub__tag">Presencial &amp; online</span>
        <h3 class="hub__titulo">Eventos</h3>
        <p class="hub__desc">Encontros presenciais e online que conectam mulheres, famílias e profissionais. Imersões, rodas de conversa e palestras com profundidade e acolhimento.</p>
        <span class="hub__go">Explorar <i class="ph ph-arrow-right" aria-hidden="true"></i></span>
      </a></li>
      <li class="rise" data-d="3"><a class="hub__card" href="/educacao/materiais-gratuitos" data-glow>
        <span class="hub__ico"><i class="ph ph-download-simple" aria-hidden="true"></i></span>
        <span class="hub__tag">Acesso livre</span>
        <h3 class="hub__titulo">Materiais gratuitos</h3>
        <p class="hub__desc">Guias, checklists e conteúdos introdutórios para quem está começando a se informar. Um primeiro passo generoso em direção ao cuidado consciente.</p>
        <span class="hub__go">Explorar <i class="ph ph-arrow-right" aria-hidden="true"></i></span>
      </a></li>
      <li class="rise" data-d="3"><a class="hub__card" href="/educacao/formacao-profissional" data-glow>
        <span class="hub__ico"><i class="ph ph-medal" aria-hidden="true"></i></span>
        <span class="hub__tag">Para profissionais</span>
        <h3 class="hub__titulo">Formação profissional</h3>
        <p class="hub__desc">Para médicos, residentes e equipes que desejam elevar seu padrão de assistência. Mentoria, supervisão e conteúdo técnico de quem vive a prática diariamente.</p>
        <span class="hub__go">Explorar <i class="ph ph-arrow-right" aria-hidden="true"></i></span>
      </a></li>
    </ol>
  </div>
</section>

<!-- ===== cena 3 — chamada final ===== -->
<!-- textos: Escopo das Páginas/Educação.md, seção 3 -->
<section class="scene educ scheme-04" id="novidades" data-scene data-tone="dark" aria-labelledby="novidades-titulo">
  <div class="scene__body">
    <div>
      <h2 class="t-h2 rise" id="novidades-titulo">Quer receber novidades<br>em primeira mão?</h2>
      <p class="educ__lead rise" data-d="1">Lançamentos, eventos e materiais gratuitos direto para você.</p>
    </div>
    <a class="btn btn--primary rise rise--zoom" data-d="2" href="/contato"><span class="btn__label">Entrar em contato <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
  </div>
</section>
@endsection

@section('pos-rodape')
@include('site.partials.barra')
@endsection
