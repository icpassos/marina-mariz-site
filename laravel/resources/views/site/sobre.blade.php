@extends('layouts.site', [
    'titulo' => 'Sobre — Dra. Marina Mariz',
    'descricao' => 'Formação, trajetória e princípios da Dra. Marina Mariz: gestação de alto risco, medicina fetal e um cuidado que une excelência técnica e presença humana.',
    'canonical' => 'https://dramarinamariz.com.br/sobre',
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/marina-retrato.webp',
    'ogTipo' => 'profile',
    'atual' => 'sobre',
])

@section('conteudo')
<!-- ===== cena 1 — apresentação ===== -->
<!-- textos: Escopo das Páginas/Sobre.md, seção 1 -->
<section class="scene phero scheme-04" id="apresentacao" data-scene data-tone="dark" aria-labelledby="sobre-titulo">
  <div class="phero__aurora a" aria-hidden="true"></div>
  <div class="phero__aurora b" aria-hidden="true"></div>
  <div class="phero__grain" aria-hidden="true"></div>
  <div class="scene__body">
    <div class="phero__copy">
      <h1 class="t-h1 rise" data-d="1" data-split id="sobre-titulo">Antes de ser<br>médica, sou mulher<br><span class="t-h1__gap">Antes de ser<br>especialista, sou mãe</span></h1>
      <p class="lead rise" data-d="2">Minha trajetória foi moldada tanto pela <strong>ciência</strong> quanto pela <strong>experiência pessoal</strong> de ser mãe. Esses dois mundos se encontram em cada atendimento que realizo.<br>Formada em <strong>medicina pela UFMG</strong>, com residência em <strong>obstetrícia</strong> e <strong>fellowship em medicina fetal</strong>, carrego na prática clínica a memória de todas as mulheres que me ensinaram o que é gestar com medo — e com esperança.</p>
      <div class="phero__actions rise" data-d="4">
        <a class="btn btn--primary" href="/contato"><span class="btn__label">Agendar consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
        <a class="btn btn--outline" href="#travessia"><span class="btn__label">Minha trajetória <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a>
      </div>
    </div>

    <div class="phero__stage rise rise--right" data-d="2">
      <figure class="brand-card">
        <img src="assets/img/marina-retrato.webp" alt="Dra. Marina Mariz com um recém-nascido no colo" width="1088" height="1600" fetchpriority="high">
        <figcaption class="brand-card__tag"><strong>Dra. Marina Mariz</strong><span>CRM-MG 48.386</span></figcaption>
      </figure>
      <svg class="phero__mark" viewBox="0 0 45.5 45.43" aria-hidden="true"><use href="#simbolo"></use></svg>
      <svg class="phero__seal" viewBox="0 0 72.93 73.54" aria-hidden="true"><use href="#badge"></use></svg>
    </div>
  </div>

  <!-- faixa de credenciais e princípios, no padrão da home -->
  <div class="about__loop phero__loop rise" data-d="5" aria-label="Formação e princípios"><ul class="about__loop-track"><li class="about__chip"><i class="ph ph-graduation-cap" aria-hidden="true"></i><span>Medicina — UFMG</span></li><li class="about__chip"><i class="ph ph-first-aid-kit" aria-hidden="true"></i><span>Residência em GO — HC/UFMG</span></li><li class="about__chip"><i class="ph ph-baby" aria-hidden="true"></i><span>Especialização em Medicina Fetal</span></li><li class="about__chip"><i class="ph ph-certificate" aria-hidden="true"></i><span>Título FEBRASGO em GO</span></li><li class="about__chip"><i class="ph ph-identification-badge" aria-hidden="true"></i><span>CRM-MG 48.386 · RQE 30.992/30.993</span></li><li class="about__chip"><i class="ph ph-microscope" aria-hidden="true"></i><span>Competência técnica atualizada</span></li><li class="about__chip"><i class="ph ph-hand-heart" aria-hidden="true"></i><span>Presença real no dia a dia</span></li><li class="about__chip"><i class="ph ph-compass" aria-hidden="true"></i><span>Autonomia para decidir</span></li><li class="about__chip"><i class="ph ph-circles-three" aria-hidden="true"></i><span>Cuidado integral</span></li><li class="about__chip"><i class="ph ph-graduation-cap" aria-hidden="true"></i><span>Medicina — UFMG</span></li><li class="about__chip"><i class="ph ph-first-aid-kit" aria-hidden="true"></i><span>Residência em GO — HC/UFMG</span></li><li class="about__chip"><i class="ph ph-baby" aria-hidden="true"></i><span>Especialização em Medicina Fetal</span></li><li class="about__chip"><i class="ph ph-certificate" aria-hidden="true"></i><span>Título FEBRASGO em GO</span></li><li class="about__chip"><i class="ph ph-identification-badge" aria-hidden="true"></i><span>CRM-MG 48.386 · RQE 30.992/30.993</span></li><li class="about__chip"><i class="ph ph-microscope" aria-hidden="true"></i><span>Competência técnica atualizada</span></li><li class="about__chip"><i class="ph ph-hand-heart" aria-hidden="true"></i><span>Presença real no dia a dia</span></li><li class="about__chip"><i class="ph ph-compass" aria-hidden="true"></i><span>Autonomia para decidir</span></li><li class="about__chip"><i class="ph ph-circles-three" aria-hidden="true"></i><span>Cuidado integral</span></li></ul></div>
</section>

<!-- ===== cena 2 — a travessia ===== -->
<!-- textos: Escopo das Páginas/Sobre.md, seção 2 -->
<section class="scene story scheme-05" id="travessia" data-scene data-tone="light" aria-labelledby="travessia-titulo">
  <div class="scene__body">
    <header class="sec__head sec__head--stack">
      <span class="eyebrow rise">Trajetória</span>
      <h2 class="t-h2 rise" data-d="1" id="travessia-titulo">A Travessia</h2>
      <p class="lead rise" data-d="2">O caminho que me levou da competência técnica ao cuidado que também acolhe<br>— contado em três tempos.</p>
    </header>

    <div class="story__grid">
      <ol class="story__steps">
        <li class="story__step rise" data-d="1">
          <span class="story__dot"><i class="ph ph-first-aid-kit" aria-hidden="true"></i></span>
          <div>
            <span class="story__label">A escolha</span>
            <h3 class="t-h3">Um campo exigente</h3>
            <p>Quando comecei a residência em obstetrícia, sabia que tinha escolhido um campo exigente. O que não sabia era que, anos depois, estaria <strong>do outro lado</strong> — como paciente, vivendo uma <strong>gestação de alto risco</strong> e entendendo, na pele, o peso do diagnóstico.</p>
          </div>
        </li>
        <li class="story__step rise" data-d="2">
          <span class="story__dot"><i class="ph ph-heartbeat" aria-hidden="true"></i></span>
          <div>
            <span class="story__label">A virada</span>
            <h3 class="t-h3">O que a técnica não alcança</h3>
            <figure class="pull">
              <blockquote><p>Essa travessia transformou minha relação com a medicina. Aprendi que a competência técnica, por mais sólida que seja, <b>não basta se não for acompanhada de escuta real, linguagem acessível e presença humana genuína.</b></p></blockquote>
              <figcaption class="pull__by"><svg viewBox="0 0 45.5 45.43" aria-hidden="true"><use href="#simbolo"></use></svg>Dra. Marina Mariz</figcaption>
            </figure>
          </div>
        </li>
        <li class="story__step rise" data-d="3">
          <span class="story__dot"><i class="ph ph-hand-heart" aria-hidden="true"></i></span>
          <div>
            <span class="story__label">Hoje</span>
            <h3 class="t-h3">Médica e mãe, no mesmo consultório</h3>
            <p>Hoje, levo essa <strong>dupla perspectiva</strong> — de médica e de mãe — para cada consulta, cada eco morfológico, cada conversa difícil. Porque acredito que a mulher merece ser <strong>informada, respeitada e sustentada</strong> em cada passo da sua gestação.</p>
          </div>
        </li>
      </ol>

      <figure class="story__media rise rise--right" data-d="2">
        <img src="assets/img/abraco.webp" alt="Dra. Marina Mariz abraçando uma paciente" width="1066" height="1600" loading="lazy" decoding="async">
        <figcaption>A escuta é parte do exame: entender a história de quem chega muda a condução de cada caso.</figcaption>
      </figure>
    </div>
  </div>
</section>

<!-- ===== selo de transição ===== -->
<div class="seal-band scheme-04" data-scene data-tone="dark">
  <div class="seal-band__inner seal-band__inner--center">
    <svg class="seal-band__seal rise rise--zoom" viewBox="0 0 45.5 45.43" aria-hidden="true"><use href="#simbolo"></use></svg>
    <p class="seal-band__text rise" data-d="1">Ginecologia, obstetrícia e medicina fetal com <b>excelência técnica</b> e <b>presença humana real</b></p>
  </div>
</div>

<!-- ===== cena 3 — o que sustenta minha prática ===== -->
<!-- textos: Escopo das Páginas/Sobre.md, seção 3 -->
<section class="scene pillars scheme-04" id="principios" data-scene data-tone="dark" aria-labelledby="pilares-titulo">
  <div class="scene__body">
    <header class="sec__head sec__head--mid">
      <span class="eyebrow rise">Fundamentos</span>
      <h2 class="t-h2 rise" data-d="1" id="pilares-titulo">O que sustenta minha prática</h2>
      <p class="lead rise" data-d="2">Quatro <strong>compromissos</strong> que atravessam cada consulta,<br>cada <strong>exame</strong> e cada <strong>decisão</strong> tomada junto com a <strong>paciente</strong>.</p>
    </header>
    <ol class="pillars__grid">
      <li class="card rise" data-d="1" data-glow>
        <span class="card__icon"><i class="ph ph-microscope" aria-hidden="true"></i></span>
        <span class="card__num">01</span>
        <h3 class="t-h3">Competência</h3>
        <p>Formação técnica sólida e atualização contínua nas áreas de gestação de alto risco e medicina fetal.</p>
      </li>
      <li class="card rise" data-d="2" data-glow>
        <span class="card__icon"><i class="ph ph-hand-heart" aria-hidden="true"></i></span>
        <span class="card__num">02</span>
        <h3 class="t-h3">Presença</h3>
        <p>Disponibilidade real — não só para exames e consultas, mas para as dúvidas, medos e decisões do dia a dia.</p>
      </li>
      <li class="card rise" data-d="3" data-glow>
        <span class="card__icon"><i class="ph ph-compass" aria-hidden="true"></i></span>
        <span class="card__num">03</span>
        <h3 class="t-h3">Autonomia</h3>
        <p>Informação clara para que cada mulher possa tomar decisões conscientes sobre sua própria saúde e gestação.</p>
      </li>
      <li class="card rise" data-d="4" data-glow>
        <span class="card__icon"><i class="ph ph-circles-three" aria-hidden="true"></i></span>
        <span class="card__num">04</span>
        <h3 class="t-h3">Integralidade</h3>
        <p>Cuidado que enxerga a mulher em sua totalidade: corpo, emoções, história, família e contexto de vida.</p>
      </li>
    </ol>
  </div>
</section>

<!-- ===== cena 4 — formação e credenciais ===== -->
<!-- textos: Escopo das Páginas/Sobre.md, seção 4 -->
<section class="scene creds scheme-05" id="formacao" data-scene data-tone="light" aria-labelledby="formacao-titulo">
  <div class="scene__body">
    <div class="creds__aside">
      <span class="eyebrow rise">Credenciais</span>
      <h2 class="t-h2 rise" data-d="1" id="formacao-titulo">Formação<br>e títulos</h2>
    </div>

    <div class="creds__body">
      <ol class="creds__list">
        <li class="creds__row rise" data-d="1">
          <span class="creds__ico"><i class="ph ph-graduation-cap" aria-hidden="true"></i></span>
          <span class="creds__name">Graduação em Medicina</span>
          <span class="creds__org">UFMG</span>
        </li>
        <li class="creds__row rise" data-d="2">
          <span class="creds__ico"><i class="ph ph-first-aid-kit" aria-hidden="true"></i></span>
          <span class="creds__name">Residência em Ginecologia e Obstetrícia</span>
          <span class="creds__org">Hospital das Clínicas / UFMG</span>
        </li>
        <li class="creds__row rise" data-d="3">
          <span class="creds__ico"><i class="ph ph-baby" aria-hidden="true"></i></span>
          <span class="creds__name">Especialização em Medicina Fetal</span>
          <span class="creds__org"></span>
        </li>
        <li class="creds__row rise" data-d="4">
          <span class="creds__ico"><i class="ph ph-certificate" aria-hidden="true"></i></span>
          <span class="creds__name">Título de Especialista em Ginecologia e Obstetrícia</span>
          <span class="creds__org">FEBRASGO</span>
        </li>
        <li class="creds__row rise" data-d="5">
          <span class="creds__ico"><i class="ph ph-medal" aria-hidden="true"></i></span>
          <span class="creds__name">Título de Especialista em Medicina Fetal</span>
          <span class="creds__org"></span>
        </li>
      </ol>

      <div class="creds__foot rise" data-d="6">
        <p class="creds__reg"><i class="ph ph-identification-badge" aria-hidden="true"></i><span class="creds__reg-value">CRM-MG 48.386 · RQE 30.992 · RQE 30.993</span></p>
          </div>
    </div>
  </div>
  <div class="marquee creds__marquee" aria-hidden="true">
    <div class="marquee__content"><span class="marquee__item">Acolhimento <i>·</i> Ciência <i>·</i> Escuta <i>·</i> Presença <i>·</i></span><span class="marquee__item">Acolhimento <i>·</i> Ciência <i>·</i> Escuta <i>·</i> Presença <i>·</i></span></div>
  </div>
</section>

<!-- ===== cena 5 — frentes de cuidado ===== -->
<!-- textos: Escopo das Páginas/Sobre.md, seção 5 -->
<section class="scene fronts scheme-04" id="atuacao" data-scene data-tone="dark" aria-labelledby="frentes-titulo">
  <div class="scene__body">
    <header class="sec__head sec__head--mid">
      <span class="eyebrow rise">Como eu trabalho</span>
      <h2 class="t-h2 rise" data-d="1" id="frentes-titulo">Seis frentes de cuidado</h2>
      <p class="lead rise" data-d="2">Da condução clínica ao trabalho em <strong>rede</strong>: o que sustenta o <strong>acompanhamento</strong>, dentro e fora do <strong>consultório</strong>.</p>
    </header>
    <ul class="fronts__grid">
      <li class="rise" data-d="1"><article class="card" data-glow>
        <span class="card__icon"><i class="ph ph-pulse" aria-hidden="true"></i></span>
        <h3 class="t-h3">Gestação de Alto Risco</h3>
        <p>Cenários complexos pedem <strong>avaliação cuidadosa</strong>, <strong>planejamento</strong> e <strong>condução</strong> baseada em <strong>evidências</strong>.</p>
      </article></li>
      <li class="rise" data-d="2"><article class="card" data-glow>
        <span class="card__icon"><i class="ph ph-chats-circle" aria-hidden="true"></i></span>
        <h3 class="t-h3">Consultas Profundas</h3>
        <p><strong>Sem pressa.</strong> <strong>Escuta real</strong>, <strong>tempo dedicado</strong> e <strong>proximidade</strong>. Cada encontro como <strong>espaço de confiança</strong>, não como linha de produção.</p>
      </article></li>
      <li class="rise" data-d="3"><article class="card" data-glow>
        <span class="card__icon"><i class="ph ph-microscope" aria-hidden="true"></i></span>
        <h3 class="t-h3">Produção Científica</h3>
        <p>Formação acadêmica, <strong>publicações</strong> e <strong>atualização contínua</strong> — apenas credenciais e atividades comprovadas.</p>
      </article></li>
      <li class="rise" data-d="4"><a class="card" data-glow href="/amara">
        <span class="card__icon"><i class="ph ph-users-three" aria-hidden="true"></i></span>
        <h3 class="t-h3">Equipe Amara</h3>
        <p><strong>Fundadora</strong> de um núcleo com 4 obstetras, enfermeiras, <strong>doulas</strong> e <strong>consultoras</strong>. <strong>Backup confiável</strong> que conhece sua história.</p>
        <span class="card__link">Conhecer a Amara <i class="ph ph-arrow-right" aria-hidden="true"></i></span>
      </a></li>
      <li class="rise" data-d="5"><a class="card" data-glow href="/educacao">
        <span class="card__icon"><i class="ph ph-book-open" aria-hidden="true"></i></span>
        <h3 class="t-h3">Educação e Transparência</h3>
        <p><strong>Informação clara</strong> e <strong>orientação segura</strong> dentro e fora do consultório. <strong>Você entende cada decisão</strong> e participa ativamente.</p>
        <span class="card__link">Ver Educação <i class="ph ph-arrow-right" aria-hidden="true"></i></span>
      </a></li>
      <li class="rise" data-d="6"><article class="card" data-glow>
        <span class="card__icon"><i class="ph ph-share-network" aria-hidden="true"></i></span>
        <h3 class="t-h3">Trabalho em Rede</h3>
        <p><strong>Diálogo constante</strong> com colegas de obstetrícia, medicina fetal e áreas afins. <strong>Casos discutidos em conjunto</strong> sempre que a decisão pede mais de um olhar.</p>
      </article></li>
    </ul>
  </div>
</section>

<!-- ===== cena 6 — excelência e acolhimento ===== -->
<!-- textos: Escopo das Páginas/Sobre.md, seção 6 -->
<section class="scene duo scheme-05" id="cuidado" data-scene data-tone="light" aria-labelledby="duo-titulo">
  <div class="scene__body">
    <header class="sec__head sec__head--mid">
      <span class="eyebrow rise">Duas forças, um cuidado</span>
      <h2 class="t-h2 rise" data-d="1" id="duo-titulo">Excelência técnica<br>e acolhimento</h2>
      <p class="lead rise" data-d="2"><strong>Competência</strong> que antecipa riscos e <strong>escuta</strong> que sustenta a <strong>travessia</strong>. Nenhuma das duas funciona sozinha.</p>
    </header>

    <div class="duo__grid">
      <article class="card duo__card rise rise--left" data-d="1" data-glow>
        <span class="card__icon"><i class="ph ph-microscope" aria-hidden="true"></i></span>
        <h3>Excelência Técnica</h3>
        <p>Atuação em <strong>obstetrícia de alto risco</strong>, <strong>medicina fetal</strong> e <strong>gestações gemelares</strong>, com formação acadêmica e atualização contínua — apenas títulos e atividades comprovados.</p>
        <div class="duo__out">
          <div class="duo__out-head"><i class="ph ph-shield-check" aria-hidden="true"></i><span>Segurança</span></div>
          <p>A tranquilidade de estar sob cuidado de quem antecipa riscos e conduz cada decisão com base nas evidências científicas mais atuais.</p>
        </div>
        <a class="btn btn--primary duo__card-cta" href="/contato"><span class="btn__label">Quero excelência técnica <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
      </article>

      <article class="card duo__card rise rise--right" data-d="1" data-glow>
        <span class="card__icon"><i class="ph ph-hand-heart" aria-hidden="true"></i></span>
        <h3>Acolhimento como Ferramenta</h3>
        <p>Consultas profundas, <strong>escuta ativa</strong> e interesse genuíno pela história de cada paciente. Uma abordagem construída a partir de sua própria <strong>trajetória de superação</strong> como mulher, mãe e médica.</p>
        <div class="duo__out">
          <div class="duo__out-head"><i class="ph ph-heart" aria-hidden="true"></i><span>Confiança</span></div>
          <p>A certeza de ser acompanhada por uma médica que é também uma aliada. Um espaço seguro para acolher emoções e atravessar a gestação com serenidade.</p>
        </div>
        <a class="btn btn--primary duo__card-cta" href="/contato"><span class="btn__label">Quero acolhimento <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
      </article>
    </div>

  </div>
</section>
@endsection

@section('pos-conteudo')
<!-- ===== partial: layouts/partials/cta — texto de Sobre.md, seção 7 ===== -->
<section class="scene cta scheme-04" id="cta" data-scene data-tone="dark" aria-labelledby="cta-titulo">
  <div class="scene__body">
    <h2 class="t-h2 rise" id="cta-titulo">Pronta para conhecer<br>essa forma de cuidar?</h2>
    <p class="lead rise" data-d="1"><span>Agende uma consulta e descubra o que significa ser acompanhada com excelência técnica e presença humana real.</span></p>
    <div class="cta__action rise rise--zoom" data-d="2"><a class="btn btn--primary" href="/contato"><span class="btn__label">Agendar consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a></div>
  </div>
</section>
@endsection

@section('pos-rodape')
@include('site.partials.barra')
@endsection
