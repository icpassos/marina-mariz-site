@extends('layouts.site', [
    'titulo' => 'Dra. Marina Mariz — Ginecologia e Obstetrícia',
    'descricao' => 'Do positivo ao pós-parto, um modelo de cuidado que respeita todas as camadas do que é ser mulher em processo de maternar.',
    'canonical' => 'https://dramarinamariz.com.br/',
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/marina-retrato.webp',
    'atual' => 'inicio',
])

@section('jsonld')
{{-- Bloco literal: "@context" e "@type" sao nomes de diretiva do Blade. --}}
@verbatim
<script type="application/ld+json">{"@context":"https://schema.org","@graph":[{"@type":"WebSite","@id":"https://dramarinamariz.com.br/#site","url":"https://dramarinamariz.com.br/","name":"Dra. Marina Mariz","inLanguage":"pt-BR","publisher":{"@id":"https://dramarinamariz.com.br/#medica"}},{"@type":"Physician","@id":"https://dramarinamariz.com.br/#medica","name":"Dra. Marina Mariz","url":"https://dramarinamariz.com.br/","image":"https://dramarinamariz.com.br/assets/img/marina-retrato.webp","email":"contato@dramarinamariz.com.br","telephone":"+55-31-3090-2320","medicalSpecialty":["Obstetric","Gynecologic"],"identifier":[{"@type":"PropertyValue","name":"CRM","value":"CRM-MG 48.386"},{"@type":"PropertyValue","name":"RQE Ginecologia e Obstetrícia","value":"30.992"},{"@type":"PropertyValue","name":"RQE Medicina Fetal","value":"30.993"}],"address":{"@type":"PostalAddress","streetAddress":"R. Cláudio Manoel, 48 — Sala 1201","addressLocality":"Belo Horizonte","addressRegion":"MG","postalCode":"30140-100","addressCountry":"BR"},"areaServed":{"@type":"City","name":"Belo Horizonte"},"sameAs":["https://www.instagram.com/dramarinamariz","https://www.youtube.com/@SemNeuraPodcast"]}]}</script>
@endverbatim
@endsection

@section('conteudo')
<!-- ===== cena 1 — abertura ===== -->
<section class="scene intro scheme-04" id="abertura" data-scene data-tone="dark" aria-label="Abertura">
  <div class="scene__bg"><video src="assets/video/abertura.mp4" autoplay muted loop playsinline aria-hidden="true"></video></div>
  <div class="scene__veil intro__veil"></div>
  <div class="scene__body">
    <h1 class="t-h1 intro__title">
      <span class="intro__type" data-type='["Gestar não é linear.","É singular."]' aria-hidden="true"></span>
      <span class="sr-only">Gestar não é linear. É singular.</span>
    </h1>
    <div class="intro__copy">
      <p class="lead rise" data-d="1">Do positivo ao pós-parto, um modelo de cuidado que respeita todas as camadas do que é ser mulher em processo de maternar.</p>
      <p class="intro__sub rise" data-d="2">Gestação de alto risco, medicina fetal e práticas humanizadas.</p>
      <div class="intro__actions rise" data-d="3">
        <a class="btn btn--glass" href="#sobre"><span class="btn__label">Conheça a Dra. Marina</span></a>
        <a class="btn btn--glass" href="#especialidades"><span class="btn__label">Especialidades</span></a>
        <a class="btn btn--primary" href="/contato"><span class="btn__label">Agendar consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
      </div>
    </div>
  </div>
  <p class="intro__cue" aria-hidden="true"><span></span>Role</p>
</section>

<!-- ===== cena 2 — sobre ===== -->
<section class="scene about scheme-05" id="sobre" data-scene data-tone="light" aria-labelledby="sobre-titulo">
  <div class="scene__body">
    <figure class="about__photo rise rise--left">
      <img src="assets/img/marina-sobre.webp" alt="Dra. Marina Mariz sorrindo em seu consultório" width="666" height="1000" loading="lazy">
      <svg class="about__seal" viewBox="0 0 72.93 73.54" aria-hidden="true"><use href="#badge"></use></svg>
    </figure>
    <div class="about__copy">
      <span class="eyebrow rise">Sobre Marina</span>
      <h2 class="t-h2 rise" data-d="1" id="sobre-titulo">Antes de ser médica, sou mulher.<br>Antes de ser especialista, sou mãe</h2>
      <!-- texto: Escopo das Páginas/Sobre.md, seção 1 -->
      <p class="lead rise" data-d="2">Minha trajetória foi moldada tanto pela <strong>ciência</strong> quanto pela <strong>experiência pessoal</strong> de ser mãe. Esses dois mundos se encontram em cada atendimento que realizo.</p>
      <div class="about__loop rise" data-d="4" aria-label="Formação e princípios"><ul class="about__loop-track"><li class="about__chip"><i class="ph ph-graduation-cap" aria-hidden="true"></i><span>Medicina — UFMG</span></li><li class="about__chip"><i class="ph ph-first-aid-kit" aria-hidden="true"></i><span>Residência em GO — HC/UFMG</span></li><li class="about__chip"><i class="ph ph-baby" aria-hidden="true"></i><span>Especialização em Medicina Fetal</span></li><li class="about__chip"><i class="ph ph-certificate" aria-hidden="true"></i><span>Título FEBRASGO em GO</span></li><li class="about__chip"><i class="ph ph-identification-badge" aria-hidden="true"></i><span>CRM-MG 48.386 · RQE 30.992/30.993</span></li><li class="about__chip"><i class="ph ph-microscope" aria-hidden="true"></i><span>Competência técnica atualizada</span></li><li class="about__chip"><i class="ph ph-hand-heart" aria-hidden="true"></i><span>Presença real no dia a dia</span></li><li class="about__chip"><i class="ph ph-compass" aria-hidden="true"></i><span>Autonomia para decidir</span></li><li class="about__chip"><i class="ph ph-circles-three" aria-hidden="true"></i><span>Cuidado integral</span></li><li class="about__chip"><i class="ph ph-graduation-cap" aria-hidden="true"></i><span>Medicina — UFMG</span></li><li class="about__chip"><i class="ph ph-first-aid-kit" aria-hidden="true"></i><span>Residência em GO — HC/UFMG</span></li><li class="about__chip"><i class="ph ph-baby" aria-hidden="true"></i><span>Especialização em Medicina Fetal</span></li><li class="about__chip"><i class="ph ph-certificate" aria-hidden="true"></i><span>Título FEBRASGO em GO</span></li><li class="about__chip"><i class="ph ph-identification-badge" aria-hidden="true"></i><span>CRM-MG 48.386 · RQE 30.992/30.993</span></li><li class="about__chip"><i class="ph ph-microscope" aria-hidden="true"></i><span>Competência técnica atualizada</span></li><li class="about__chip"><i class="ph ph-hand-heart" aria-hidden="true"></i><span>Presença real no dia a dia</span></li><li class="about__chip"><i class="ph ph-compass" aria-hidden="true"></i><span>Autonomia para decidir</span></li><li class="about__chip"><i class="ph ph-circles-three" aria-hidden="true"></i><span>Cuidado integral</span></li></ul></div>
      <p class="rise" data-d="3" style="color:var(--text-muted)">Formada em <strong>medicina pela UFMG</strong>, com residência em <strong>obstetrícia</strong> e fellowship em <strong>medicina fetal</strong>, carrego na prática clínica <strong>a memória de todas as mulheres que me ensinaram</strong> o que é gestar com medo — e com esperança.</p>
      <div class="about__actions rise" data-d="5" style="margin-top:34px"><a class="btn btn--outline" href="/sobre"><span class="btn__label">Conheça minha história<i class="ph ph-arrow-right" aria-hidden="true"></i></span></a><a class="btn btn--outline" href="#especialidades"><span class="btn__label">Minhas especialidades<i class="ph ph-arrow-right" aria-hidden="true"></i></span></a></div>
    </div>
  </div>
</section>
<!-- ===== cena 3 — especialidades ===== -->
<section class="scene spec scheme-04" id="especialidades" data-scene data-tone="dark" aria-labelledby="esp-titulo">
  <div class="scene__body">
    <header class="spec__head">
      <span class="eyebrow rise">Especialidades</span>
      <h2 class="t-h2 rise" data-d="1" id="esp-titulo">Cuidado especializado<br>em cada fase da jornada</h2>
    </header>
    <ul class="spec__list"><li class="spec__row rise" data-d="1"><button class="spec__btn" type="button" aria-expanded="false" aria-controls="esp-1"><span class="spec__num">01</span><span class="spec__name">Gestação de Alto Risco</span><i class="ph ph-plus" aria-hidden="true"></i></button><div class="spec__panel" id="esp-1"><div><p>Acompanhamento especializado para <strong>gestações que exigem maior atenção</strong>, com <strong>protocolos</strong> baseados nas <strong>evidências</strong> mais atuais.</p></div></div></li><li class="spec__row rise" data-d="2"><button class="spec__btn" type="button" aria-expanded="false" aria-controls="esp-2"><span class="spec__num">02</span><span class="spec__name">Medicina Fetal e Ultrassonografia</span><i class="ph ph-plus" aria-hidden="true"></i></button><div class="spec__panel" id="esp-2"><div><p>Diagnóstico e <strong>acompanhamento fetal</strong> com recursos adequados, interpretação clara e cuidado <strong>individualizado</strong>.</p></div></div></li><li class="spec__row rise" data-d="3"><button class="spec__btn" type="button" aria-expanded="false" aria-controls="esp-3"><span class="spec__num">03</span><span class="spec__name">Parto Humanizado</span><i class="ph ph-plus" aria-hidden="true"></i></button><div class="spec__panel" id="esp-3"><div><p>Acompanhamento <strong>respeitoso</strong> do trabalho de parto, com <strong>decisões partilhadas</strong> e <strong>protagonismo</strong> de quem pare.</p></div></div></li><li class="spec__row rise" data-d="4"><button class="spec__btn" type="button" aria-expanded="false" aria-controls="esp-4"><span class="spec__num">04</span><span class="spec__name">Puerpério e Pós-parto</span><i class="ph ph-plus" aria-hidden="true"></i></button><div class="spec__panel" id="esp-4"><div><p><strong>Suporte integral</strong> no quarto trimestre, cuidando do <strong>corpo</strong> e da <strong>mente</strong> na <strong>transição</strong> para a maternidade.</p></div></div></li><li class="spec__row rise" data-d="5"><button class="spec__btn" type="button" aria-expanded="false" aria-controls="esp-5"><span class="spec__num">05</span><span class="spec__name">Pré-concepção e Planejamento Familiar</span><i class="ph ph-plus" aria-hidden="true"></i></button><div class="spec__panel" id="esp-5"><div><p>Preparo do <strong>corpo</strong> e da <strong>rotina</strong> antes da gestação: <strong>exames</strong>, <strong>orientação</strong> e escolha do <strong>método</strong> contraceptivo.</p></div></div></li><li class="spec__row rise" data-d="6"><button class="spec__btn" type="button" aria-expanded="false" aria-controls="esp-6"><span class="spec__num">06</span><span class="spec__name">Saúde da Mulher</span><i class="ph ph-plus" aria-hidden="true"></i></button><div class="spec__panel" id="esp-6"><div><p>Ginecologia geral em todas as fases: <strong>consultas</strong> de rotina, <strong>rastreios</strong>, <strong>contracepção</strong> e <strong>climatério</strong>.</p></div></div></li></ul>
    <div class="spec__cta rise" data-d="4"><a class="btn btn--outline" href="/especialidades"><span class="btn__label">Saber mais sobre as especialidades <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a><a class="btn btn--primary" href="/contato"><span class="btn__label">Agendar consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a></div>
  </div>
</section>

<!-- ===== cena 4 — amara ===== -->
<section class="scene team scheme-05" id="amara" data-scene data-tone="light" aria-labelledby="amara-titulo">
  <div class="scene__body">
    <div class="team__copy">
      <span class="eyebrow rise">A Equipe</span>
      <h2 class="t-h2 rise" data-d="1" id="amara-titulo">Você nunca está<br>sozinha nessa jornada</h2>
      <div class="team__card rise rise--zoom" data-d="1"><svg viewBox="0 0 659.72 87.45" aria-hidden="true"><use href="#amara-logo"></use></svg><p>A Dra. Marina Mariz faz parte da Amara</p></div>
      <!-- texto: Escopo das Páginas/Amara.md, seção 2 -->
      <p class="lead rise" data-d="2">A Amara é um <strong>coletivo multidisciplinar</strong> que reúne profissionais <strong>cuidadosamente selecionados</strong> pela Dra. Marina Mariz — unidos não apenas pela <strong>excelência técnica</strong>, mas por uma <strong>filosofia de prática compartilhada</strong>.</p>
      <p class="rise" data-d="3" style="color:var(--text-muted)">Aqui, cada profissional conhece o trabalho dos demais. As decisões são tomadas <strong>em conjunto</strong>. A comunicação é <strong>fluida</strong>. E a mulher — sempre no centro — recebe um cuidado que é <strong>maior do que a soma das partes</strong>.</p>
      <div class="team__actions rise" data-d="4" style="margin-top:34px"><a class="btn btn--outline" href="/amara"><span class="btn__label">Conhecer a equipe <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a><a class="btn btn--primary" href="/contato"><span class="btn__label">Agendar consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a></div>
    </div>
    <div class="team__frame rise rise--right" data-d="2" aria-hidden="true"><img src="assets/img/amara/1.webp" alt="" width="675" height="900" loading="lazy" decoding="async"><img src="assets/img/amara/19.webp" alt="" width="642" height="900" loading="lazy" decoding="async"><img src="assets/img/amara/10.webp" alt="" width="1280" height="851" loading="lazy" decoding="async"><img src="assets/img/amara/12.webp" alt="" width="900" height="598" loading="lazy" decoding="async"><img src="assets/img/amara/15.webp" alt="" width="599" height="900" loading="lazy" decoding="async"><img src="assets/img/amara/17.webp" alt="" width="931" height="1400" loading="lazy" decoding="async"><svg class="team__seal" viewBox="0 0 149.31 148.64" aria-hidden="true"><use href="#amara-badge"></use></svg></div>
  </div>
</section>

<!-- ===== cena 5 — comunidade ===== -->
<section class="scene community scheme-02" id="comunidade" data-scene data-tone="light" aria-labelledby="com-titulo">
  <div class="scene__body">
    <div class="community__id">
      <img class="community__logo rise rise--left" data-d="1" id="com-titulo" src="assets/logo/comunidade-sem-neura.svg" alt="Comunidade Sem Neura" width="353" height="92" loading="lazy">
    </div>
    <div class="community__text">
      <p class="lead rise" data-d="1">A maternidade vem cheia de dúvidas. Agora você sabe onde encontrar respostas confiáveis.</p>
      <p class="rise" data-d="2">Uma <strong>comunidade criada pela Dra. Marina Mariz</strong>, onde a ciência sobre <strong>pré-concepção</strong>, <strong>gestação</strong>, <strong>parto</strong> e <strong>puerpério</strong> chega até você em <strong>linguagem clara</strong>, na companhia de <strong>mulheres que vivem a mesma fase</strong>.</p>
    </div>
    <div class="community__cta rise rise--zoom" data-d="4"><a class="btn btn--primary" href="https://comunidade.dramarinamariz.com.br" target="_blank" rel="noopener"><span class="btn__label">Conhecer a Comunidade <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a></div>
  </div>
</section>

<!-- ===== cena 6 — blog ===== -->
<!-- textos: Escopo das Páginas/Blog.md, seções 1 e 2 -->
<section class="scene blog scheme-04" id="blog" data-scene data-tone="dark" aria-labelledby="blog-titulo">
  <div class="scene__body">
    <header class="blog__head">
      <span class="eyebrow rise">Blog</span>
      <h2 class="t-h2 rise" data-d="1" id="blog-titulo">Transforme sua experiência</h2>
      <p class="lead rise" data-d="2"><strong>Artigos e informações produzidos pela Dra. Marina Mariz</strong> para que você possa navegar pela maternidade com mais <strong>clareza e segurança</strong>.</p>
    </header>
    <ul class="blog__list"><li class="blog__row rise" data-d="1"><a href="/blog"><span class="blog__cat">Artigos</span><span class="blog__title">Gestação de alto risco: o que toda mulher precisa saber</span><span class="blog__date">Jan 2025</span><i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></li><li class="blog__row rise" data-d="2"><a href="/blog"><span class="blog__cat">Artigos</span><span class="blog__title">Medicina fetal: quando começa o cuidado com o bebê?</span><span class="blog__date">Fev 2025</span><i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></li><li class="blog__row rise" data-d="3"><a href="/blog"><span class="blog__cat">Artigos</span><span class="blog__title">Pós-parto: o que ninguém te conta sobre o quarto trimestre</span><span class="blog__date">Mar 2025</span><i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></li><li class="blog__row rise" data-d="4"><a href="/blog"><span class="blog__cat">Episódios</span><span class="blog__title">Ep. 12 — Pré-eclâmpsia: mitos e verdades</span><span class="blog__date">Jan 2025</span><i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></li><li class="blog__row rise" data-d="5"><a href="/blog"><span class="blog__cat">Episódios</span><span class="blog__title">Ep. 13 — Saúde mental na gestação com Dra. Ana Lima</span><span class="blog__date">Fev 2025</span><i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></li><li class="blog__row rise" data-d="6"><a href="/blog"><span class="blog__cat">Materiais</span><span class="blog__title">Guia completo do pré-natal de alto risco</span><span class="blog__date">Dez 2024</span><i class="ph ph-arrow-up-right" aria-hidden="true"></i></a></li></ul>
    <div class="blog__cta rise" data-d="4"><a class="btn btn--outline" href="/blog"><span class="btn__label">Ir para o Blog <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a><a class="btn btn--outline" href="/sobre"><span class="btn__label">Conhecer mais sobre a Dra. Marina <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a><a class="btn btn--primary" href="/contato"><span class="btn__label">Agendar consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a></div>
  </div>
</section>

<!-- ===== cena 7 — educação ===== -->
<!-- textos: Escopo das Páginas/Educação.md, seções 1 e 2 -->
<section class="scene edu scheme-05" id="educacao" data-scene data-tone="light" aria-labelledby="edu-titulo">
  <div class="scene__body">
    <header class="edu__head">
      <span class="eyebrow rise">Educação</span>
      <h2 class="t-h2 rise" data-d="1" id="edu-titulo">Amplie seus<br>conhecimentos</h2>
      <p class="lead rise" data-d="2"><strong>Para gestantes, famílias e profissionais.</strong> A <strong>área de educação</strong> da Dra. Marina Mariz amplia o acesso a um novo <strong>padrão de excelência</strong> no cuidado <strong>materno-fetal</strong>.</p>
    </header>
    <ul class="edu__grid"><li class="edu__card rise rise--zoom" data-d="1"><a href="/educacao/livros" data-glow><span class="card__icon"><i class="ph ph-book-open" aria-hidden="true"></i></span><span class="edu__tag">Publicações</span><strong class="t-h3">Livros</strong><span class="edu__go">Explorar <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a></li><li class="edu__card rise rise--zoom" data-d="2"><a href="/educacao/ebooks" data-glow><span class="card__icon"><i class="ph ph-file-text" aria-hidden="true"></i></span><span class="edu__tag">Digital</span><strong class="t-h3">E-books</strong><span class="edu__go">Explorar <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a></li><li class="edu__card rise rise--zoom" data-d="3"><a href="/educacao/cursos" data-glow><span class="card__icon"><i class="ph ph-graduation-cap" aria-hidden="true"></i></span><span class="edu__tag">Formação</span><strong class="t-h3">Cursos</strong><span class="edu__go">Explorar <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a></li><li class="edu__card rise rise--zoom" data-d="4"><a href="/educacao/eventos" data-glow><span class="card__icon"><i class="ph ph-calendar-blank" aria-hidden="true"></i></span><span class="edu__tag">Presencial & Online</span><strong class="t-h3">Eventos</strong><span class="edu__go">Explorar <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a></li><li class="edu__card rise rise--zoom" data-d="5"><a href="/educacao/materiais-gratuitos" data-glow><span class="card__icon"><i class="ph ph-download-simple" aria-hidden="true"></i></span><span class="edu__tag">Acesso Livre</span><strong class="t-h3">Materiais Gratuitos</strong><span class="edu__go">Explorar <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a></li><li class="edu__card rise rise--zoom" data-d="6"><a href="/educacao/formacao-profissional" data-glow><span class="card__icon"><i class="ph ph-medal" aria-hidden="true"></i></span><span class="edu__tag">Para Profissionais</span><strong class="t-h3">Formação Profissional</strong><span class="edu__go">Explorar <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a></li></ul>
    <div class="edu__cta rise" data-d="4"><a class="btn btn--outline" href="/educacao"><span class="btn__label">Ver tudo em Educação <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a></div>
  </div>
</section>

<!-- ===== cena 8 — podcast ===== -->
<section class="scene podcast scheme-04" id="podcast" data-scene data-tone="dark" aria-labelledby="pod-titulo">
  <div class="scene__body">
    <div class="podcast__id">
      <img class="podcast__logo rise rise--left" data-d="1" id="pod-titulo" src="assets/logo/podcast-sem-neura.svg" alt="Sem Neura Podcast" width="172" height="64" loading="lazy">
    </div>
    <div class="wave rise rise--drop" data-d="2" aria-hidden="true"><span style="animation-delay:0.00s"></span><span style="animation-delay:-0.09s"></span><span style="animation-delay:-0.18s"></span><span style="animation-delay:-0.27s"></span><span style="animation-delay:-0.36s"></span><span style="animation-delay:-0.45s"></span><span style="animation-delay:-0.54s"></span><span style="animation-delay:-0.63s"></span><span style="animation-delay:-0.72s"></span><span style="animation-delay:-0.81s"></span><span style="animation-delay:-0.90s"></span><span style="animation-delay:-0.99s"></span><span style="animation-delay:-1.08s"></span><span style="animation-delay:-1.17s"></span><span style="animation-delay:-1.26s"></span><span style="animation-delay:-1.35s"></span><span style="animation-delay:-1.44s"></span><span style="animation-delay:-1.53s"></span><span style="animation-delay:-1.62s"></span><span style="animation-delay:-1.71s"></span><span style="animation-delay:-1.80s"></span><span style="animation-delay:-1.89s"></span><span style="animation-delay:-1.98s"></span><span style="animation-delay:-2.07s"></span><span style="animation-delay:-2.16s"></span><span style="animation-delay:-2.25s"></span><span style="animation-delay:-2.34s"></span><span style="animation-delay:-2.43s"></span><span style="animation-delay:-2.52s"></span><span style="animation-delay:-2.61s"></span><span style="animation-delay:-2.70s"></span><span style="animation-delay:-2.79s"></span><span style="animation-delay:-2.88s"></span><span style="animation-delay:-2.97s"></span></div>
    <div class="podcast__copy">
      <p class="podcast__hosts rise" data-d="1">Com Marina Mariz e Carol Flores</p>
      <p class="rise" data-d="3">Um espaço de <strong>conversa</strong>, <strong>informação</strong> e <strong>acolhimento</strong> para quem está tentando <strong>engravidar</strong>, <strong>gestando</strong> ou vivendo o <strong>puerpério</strong>.</p>
      <p class="rise" data-d="3">Em breve, o Sem Neura Podcast reestreia cheio de novidades.</p>
    </div>
    <div class="podcast__cta rise rise--right" data-d="4"><a class="btn btn--outline" href="/podcast"><span class="btn__label">Conhecer o Podcast <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a></div>
  </div>
</section>
@endsection

@section('pos-conteudo')
<!-- ===== partial: layouts/partials/cta ===== -->
<section class="scene cta scheme-04" id="cta" data-scene data-tone="dark" aria-labelledby="cta-titulo">
  <div class="scene__body">
    <h2 class="t-h2 rise" id="cta-titulo">Pronta para conversar<br>sobre o seu cuidado?</h2>
    <p class="lead rise" data-d="1"><span>O primeiro passo é simples: uma conversa.<br>Vamos entender juntas o que você precisa e como posso ajudar.</span></p>
    <div class="cta__action rise rise--zoom" data-d="2"><a class="btn btn--primary" href="/contato"><span class="btn__label">Agendar consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a></div>
  </div>
</section>
@endsection

@section('pos-rodape')
@include('site.partials.barra')
@endsection
