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
      <h1 class="t-h1 rise" data-d="1" data-split id="sobre-titulo">Mulher. Médica.<br>Obstetra. Mãe.<br><span class="t-h1__gap">Minha prática nasce<br>de tudo o que sou.</span></h1>
      <p class="lead rise" data-d="2"><strong>Duas formações</strong>: a que veio da ciência e a que a maternidade <br class="br-desk">me deu. Esses dois mundos se encontram em cada atendimento que realizo.</p>
      <p class="lead rise" data-d="3">Uma gestação pode exigir protocolos, exames e condutas muito precisas. Mas cuidar <br class="br-desk">de uma mulher exige <strong>enxergar além deles</strong>. É nesse equilíbrio entre evidências, individualização, contexto e humanidade que encontro a forma mais responsável <br class="br-desk">e acolhedora possível para exercer a medicina.</p>
      <div class="phero__actions rise" data-d="4">
        <a class="btn btn--primary" href="/contato"><span class="btn__label">Agendar consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
        <a class="btn btn--outline" href="#travessia"><span class="btn__label">Minha atuação <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a>
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
  <div class="about__loop phero__loop rise" data-d="5" aria-label="Formação e princípios"><ul class="about__loop-track"><li class="about__chip"><i class="ph ph-graduation-cap" aria-hidden="true"></i><span>Medicina — UFMG</span></li><li class="about__chip"><i class="ph ph-first-aid-kit" aria-hidden="true"></i><span>Residência em GO — HC/UFMG</span></li><li class="about__chip"><i class="ph ph-baby" aria-hidden="true"></i><span>Especialização em Medicina Fetal</span></li><li class="about__chip"><i class="ph ph-certificate" aria-hidden="true"></i><span>Título FEBRASGO em GO</span></li><li class="about__chip"><i class="ph ph-identification-badge" aria-hidden="true"></i><span>CRM-MG 48.386 · RQE 30.992/30.993</span></li><li class="about__chip"><i class="ph ph-microscope" aria-hidden="true"></i><span>Gestações de alto risco</span></li><li class="about__chip"><i class="ph ph-hand-heart" aria-hidden="true"></i><span>Práticas humanizadas</span></li><li class="about__chip"><i class="ph ph-users" aria-hidden="true"></i><span>Gestações múltiplas</span></li><li class="about__chip"><i class="ph ph-circles-three" aria-hidden="true"></i><span>Cuidado integral</span></li><li class="about__chip"><i class="ph ph-graduation-cap" aria-hidden="true"></i><span>Medicina — UFMG</span></li><li class="about__chip"><i class="ph ph-first-aid-kit" aria-hidden="true"></i><span>Residência em GO — HC/UFMG</span></li><li class="about__chip"><i class="ph ph-baby" aria-hidden="true"></i><span>Especialização em Medicina Fetal</span></li><li class="about__chip"><i class="ph ph-certificate" aria-hidden="true"></i><span>Título FEBRASGO em GO</span></li><li class="about__chip"><i class="ph ph-identification-badge" aria-hidden="true"></i><span>CRM-MG 48.386 · RQE 30.992/30.993</span></li><li class="about__chip"><i class="ph ph-microscope" aria-hidden="true"></i><span>Gestações de alto risco</span></li><li class="about__chip"><i class="ph ph-hand-heart" aria-hidden="true"></i><span>Práticas humanizadas</span></li><li class="about__chip"><i class="ph ph-users" aria-hidden="true"></i><span>Gestações múltiplas</span></li><li class="about__chip"><i class="ph ph-circles-three" aria-hidden="true"></i><span>Cuidado integral</span></li></ul></div>
</section>

<!-- ===== cena 2 — a travessia ===== -->
<!-- textos: Escopo das Páginas/Sobre.md, seção 2 -->
<section class="scene story scheme-05" id="travessia" data-scene data-tone="light" aria-labelledby="travessia-titulo">
  <div class="scene__body">
    <header class="sec__head sec__head--stack">
      <span class="eyebrow rise">Trajetória</span>
      <h2 class="t-h2 rise" data-d="1" id="travessia-titulo">A Travessia</h2>
      <p class="lead rise" data-d="2">A ciência me dá precisão. A maternidade me dá profundidade.</p>
    </header>

    <div class="story__grid">
      <ol class="story__steps">
        <li class="story__step rise" data-d="1">
          <span class="story__dot"><i class="ph ph-first-aid-kit" aria-hidden="true"></i></span>
          <div>
            <span class="story__label">A base</span>
            <h3 class="t-h3">Conhecimento, técnica e ciência</h3>
            <p>Minha formação foi construída em um campo que exige <strong>rigor, responsabilidade e capacidade de decidir</strong> diante da complexidade. Por muito tempo, a excelência técnica ocupou o centro de tudo: reconhecer riscos, antecipar problemas, dominar protocolos e buscar a melhor conduta possível.</p>
          </div>
        </li>
        <li class="story__step rise" data-d="2">
          <span class="story__dot"><i class="ph ph-heartbeat" aria-hidden="true"></i></span>
          <div>
            <span class="story__label">Do outro lado do cuidado</span>
            <h3 class="t-h3">A experiência que amplia o olhar</h3>
            <p>Vivi <strong>perdas gestacionais</strong> e, mais tarde, uma <strong>gestação gemelar difícil</strong>, marcada por complicações, medo, espera e pela constatação de que nem tudo pode ser controlado.</p>
            <figure class="pull">
              <blockquote><p>Compreendi que técnica não existe para controlar a vida, mas para protegê-la. Que é possível antecipar riscos sem silenciar emoções, dominar a complexidade sem endurecer o cuidado e <b>oferecer segurança sem ignorar a singularidade de quem está sendo cuidada.</b></p></blockquote>
              <figcaption class="pull__by"><svg viewBox="0 0 45.5 45.43" aria-hidden="true"><use href="#simbolo"></use></svg>Dra. Marina Mariz</figcaption>
            </figure>
          </div>
        </li>
        <li class="story__step rise" data-d="3">
          <span class="story__dot"><i class="ph ph-hand-heart" aria-hidden="true"></i></span>
          <div>
            <span class="story__label">Hoje</span>
            <h3 class="t-h3">Ciência e acolhimento na mesma prática</h3>
            <p>É desse encontro que nasce a forma como exerço a medicina hoje: <strong>consultas generosas em tempo</strong>, especializações, atualizações, <strong>escuta real</strong>, proximidade e <strong>decisões compartilhadas</strong>. Sem automatizar protocolos. Sem transformar histórias em prontuários. Sem tratar o cuidado como linha de produção.</p>
          </div>
        </li>
      </ol>

      <figure class="story__media rise rise--right" data-d="2">
        <img src="assets/img/travessia-01.webp" alt="Dra. Marina Mariz grávida, sentada em casa" width="1024" height="1024" loading="lazy" decoding="async">
        <img src="assets/img/travessia-02.webp" alt="" width="1080" height="1350" loading="lazy" decoding="async">
        <img src="assets/img/travessia-03.webp" alt="" width="1080" height="1350" loading="lazy" decoding="async">
        <img src="assets/img/imagem-007.webp" alt="" width="1135" height="1386" loading="lazy" decoding="async">
        <img src="assets/img/travessia-05.webp" alt="" width="1000" height="1501" loading="lazy" decoding="async">
        <img src="assets/img/travessia-06.webp" alt="" width="1000" height="1501" loading="lazy" decoding="async">
        <img src="assets/img/travessia-07.webp" alt="" width="1000" height="1500" loading="lazy" decoding="async">
        <img src="assets/img/travessia-08.webp" alt="" width="652" height="844" loading="lazy" decoding="async">
        <img src="assets/img/travessia-09.webp" alt="" width="552" height="817" loading="lazy" decoding="async">
        <figcaption>Lidar com a complexidade do nascimento exige não simplificar quem vive essa experiência.</figcaption>
      </figure>
    </div>
  </div>
</section>

<!-- ===== selo de transição ===== -->
<div class="seal-band scheme-04" data-scene data-tone="dark">
  <div class="seal-band__inner seal-band__inner--center">
    <svg class="seal-band__seal rise rise--zoom" viewBox="0 0 45.5 45.43" aria-hidden="true"><use href="#simbolo"></use></svg>
    <p class="seal-band__text rise" data-d="1">Ginecologia e Obstetrícia com foco em <b>gestação de alto risco, medicina fetal e práticas humanizadas</b></p>
  </div>
</div>

<!-- ===== cena 3 — o que sustenta minha prática ===== -->
<!-- textos: Escopo das Páginas/Sobre.md, seção 3 -->
<section class="scene pillars scheme-04" id="principios" data-scene data-tone="dark" aria-labelledby="pilares-titulo">
  <div class="scene__body">
    <header class="sec__head sec__head--mid">
      <span class="eyebrow rise">Fundamentos</span>
      <h2 class="t-h2 rise" data-d="1" id="pilares-titulo">O que sustenta a minha prática</h2>
      <p class="lead rise" data-d="2">Quatro <strong>pilares</strong> que orientam a forma como <strong>cuido de cada paciente</strong>.</p>
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
        <h3 class="t-h3">Acolhimento</h3>
        <p>Escuta sem pressa e espaço para que dúvidas, medos e escolhas sejam tratados com a seriedade que merecem.</p>
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
          <span class="creds__org">UFMG</span>
        </li>
        <li class="creds__row rise" data-d="4">
          <span class="creds__ico"><i class="ph ph-certificate" aria-hidden="true"></i></span>
          <span class="creds__name">Título de Especialista em Ginecologia e Obstetrícia</span>
          <span class="creds__org">FEBRASGO</span>
        </li>
        <li class="creds__row rise" data-d="5">
          <span class="creds__ico"><i class="ph ph-medal" aria-hidden="true"></i></span>
          <span class="creds__name">Título de Especialista em Medicina Fetal</span>
          <span class="creds__org">UFMG</span>
        </li>
      </ol>

      <div class="creds__foot rise" data-d="6">
        <p class="creds__reg"><i class="ph ph-identification-badge" aria-hidden="true"></i><span class="creds__reg-value">CRM-MG 48.386 · RQE 30.992 · RQE 30.993</span></p>
          </div>
    </div>
  </div>
  <div class="marquee creds__marquee" aria-hidden="true">
    <div class="marquee__content"><span class="marquee__item">Acolhimento <i>·</i> Ciência <i>·</i> Escuta <i>·</i> Confiança <i>·</i></span><span class="marquee__item">Acolhimento <i>·</i> Ciência <i>·</i> Escuta <i>·</i> Confiança <i>·</i></span></div>
  </div>
</section>
@endsection

@section('pos-conteudo')
<!-- ===== partial: layouts/partials/cta — texto de Sobre.md, seção 7 ===== -->
<section class="scene cta scheme-04" id="cta" data-scene data-tone="dark" aria-labelledby="cta-titulo">
  <div class="scene__body">
    <h2 class="t-h2 rise" id="cta-titulo">Pronta para começar o seu<br>pré-natal com a Dra. Marina?</h2>
    <p class="lead rise" data-d="1"><span>Agende sua consulta e conheça uma forma de <br class="br-desk">acompanhamento que une ciência, escuta e acolhimento.</span></p>
    <div class="cta__action rise rise--zoom" data-d="2"><a class="btn btn--primary" href="/contato"><span class="btn__label">Agendar consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a></div>
  </div>
</section>
@endsection

@section('pos-rodape')
@include('site.partials.barra')
@endsection
