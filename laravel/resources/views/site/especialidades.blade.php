@extends('layouts.site', [
    'titulo' => 'Especialidades — Dra. Marina Mariz',
    'descricao' => 'Gestação de alto risco, medicina fetal, parto humanizado, puerpério, pré-concepção e saúde da mulher: as seis frentes de cuidado da Dra. Marina Mariz, em Belo Horizonte.',
    'canonical' => 'https://dramarinamariz.com.br/especialidades',
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/consultorio.webp',
    'atual' => 'especialidades',
])

@section('conteudo')
<!-- ===== cena 1 — herói ===== -->
<!-- textos: Escopo das Páginas/Especialidades.md, seção 1 -->
<section class="scene esph scheme-04" id="apresentacao" data-scene data-tone="dark" aria-labelledby="esp-titulo">
  <div class="scene__bg" aria-hidden="true"><img src="assets/img/consultorio.webp" data-depth="0.07" alt="" width="1066" height="1600" fetchpriority="high"></div>
  <div class="scene__veil" aria-hidden="true"></div>
  <div class="esph__halo" aria-hidden="true"></div>
  <div class="esph__grain" aria-hidden="true"></div>
  <div class="scene__body">
    <div class="esph__copy">
      <h1 class="t-h1 rise" data-d="1" data-split id="esp-titulo">Do planejamento<br>ao pós-parto</h1>
      <p class="lead rise" data-d="2">Cuidado que começa <strong>antes da gestação</strong> e segue <strong>depois dela</strong>.</p>
      <p class="lead rise" data-d="3"><strong>Seis frentes</strong>, uma mesma médica e o mesmo padrão em todas elas: <strong>decisão baseada em evidência</strong>, <strong>linguagem clara</strong> e <strong>tempo real de escuta</strong>.</p>

      <figure class="pull rise" data-d="5">
        <blockquote><p>Nenhum protocolo substitui conhecer a história de quem está na sua frente. A técnica define o que é possível; <b>a escuta define o que é certo para aquela mulher.</b></p></blockquote>
        <figcaption class="pull__by"><svg viewBox="0 0 45.5 45.43" aria-hidden="true"><use href="#simbolo"></use></svg>Dra. Marina Mariz</figcaption>
      </figure>
    </div>

    <div class="esph__side rise rise--right" data-d="2">
    <nav class="esph__index" aria-label="Índice das especialidades">
      <span class="esph__index-tag">Seis frentes de cuidado</span>
      <ol>
        <li><a href="#area-01"><span class="esph__n">01</span><span>Pré-concepção</span><i class="ph ph-arrow-right" aria-hidden="true"></i></a></li>
        <li><a href="#area-02"><span class="esph__n">02</span><span>Pré-natal</span><i class="ph ph-arrow-right" aria-hidden="true"></i></a></li>
        <li><a href="#area-03"><span class="esph__n">03</span><span>Gestação de Alto Risco</span><i class="ph ph-arrow-right" aria-hidden="true"></i></a></li>
        <li><a href="#area-04"><span class="esph__n">04</span><span>Medicina Fetal</span><i class="ph ph-arrow-right" aria-hidden="true"></i></a></li>
        <li><a href="#area-05"><span class="esph__n">05</span><span>Assistência Personalizada</span><i class="ph ph-arrow-right" aria-hidden="true"></i></a></li>
        <li><a href="#area-06"><span class="esph__n">06</span><span>Parto Humanizado</span><i class="ph ph-arrow-right" aria-hidden="true"></i></a></li>
      </ol>
    </nav>

      <div class="esph__actions">
        <a class="btn btn--primary" href="/contato"><span class="btn__label">Agendar consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
        <a class="btn btn--outline" href="/sobre"><span class="btn__label">Conhecer a Dra. Marina <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a>
      </div>
    </div>
  </div>

  <div class="esph__band marquee" aria-hidden="true">
    <div class="marquee__content">
      <span class="marquee__item">Pré-concepção <i>·</i> Pré-natal <i>·</i> Alto risco <i>·</i> Medicina fetal <i>·</i> Assistência personalizada <i>·</i> Parto humanizado <i>·</i></span>
      <span class="marquee__item">Pré-concepção <i>·</i> Pré-natal <i>·</i> Alto risco <i>·</i> Medicina fetal <i>·</i> Assistência personalizada <i>·</i> Parto humanizado <i>·</i></span>
    </div>
  </div>
</section>

<!-- ===== selo de transição ===== -->
<div class="seal-band seal-band--rule scheme-05" data-scene data-tone="light">
  <div class="seal-band__inner seal-band__inner--center">
    <svg class="seal-band__seal rise rise--zoom" viewBox="0 0 45.5 45.43" aria-hidden="true"><use href="#simbolo"></use></svg>
    <p class="seal-band__text rise" data-d="1">Do plano de engravidar ao retorno depois do parto: <b>suporte, acolhimento e segurança</b></p>
  </div>
</div>

<!-- ===== cena 2 — listagem das especialidades ===== -->
<!-- textos: Escopo das Páginas/Especialidades.md, seção 2 -->
<section class="scene tri scheme-05" id="areas" data-scene data-tone="light" aria-labelledby="areas-titulo">
  <div class="scene__body">
    <header class="sec__head sec__head--mid">
      <h2 class="t-h2 rise" data-d="1" id="areas-titulo">Cuidado completo e personalizado</h2>
      <p class="lead rise" data-d="2">Cada fase pede uma <strong>condução própria</strong>. Em todas elas, ciência, segurança, informação clara e <strong>respeito às escolhas da mulher</strong>.</p>
    </header>

    <ol class="tri__grid">
      <li class="rise rise--left" id="area-01" data-d="1"><article class="tri__card" data-glow>
        <figure class="tri__media">
          <img src="assets/img/planejamento.webp" alt="Dra. Marina Mariz em ambiente de consulta, fora do hospital" width="1066" height="1600" loading="lazy" decoding="async">
        </figure>
        <div class="tri__body">
          <h3>Pré-concepção</h3>
          <p>O cuidado que começa <strong>antes do teste positivo</strong>. A consulta pré-concepcional avalia sua saúde, histórico, exames, vacinas, medicamentos e fatores de risco para preparar a paciente para uma <strong>gestação mais segura</strong>.</p>
          <ul class="tri__points">
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Avaliação clínica antes de engravidar</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Revisão de exames, vacinas e medicamentos</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Suplementação e orientação individualizada</span></li>
          </ul>
          <a class="btn btn--outline" href="/contato"><span class="btn__label">Quero me preparar <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
        </div>
      </article></li>

      <li class="rise" id="area-02" data-d="2"><article class="tri__card" data-glow>
        <figure class="tri__media">
          <img src="assets/img/saude-da-mulher.webp" alt="Quatro mulheres reunidas em ambiente iluminado" width="1141" height="1600" loading="lazy" decoding="async">
        </figure>
        <div class="tri__body">
          <h3>Pré-natal</h3>
          <p>Pré-natal é <strong>acompanhamento</strong>, não apenas uma sequência de exames. Cada consulta serve para observar a evolução da gestação, antecipar necessidades, esclarecer dúvidas e <strong>tomar decisões com segurança</strong> ao longo dos meses.</p>
          <ul class="tri__points">
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Consultas com tempo para avaliação e escuta</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Acompanhamento da saúde materno-fetal</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Orientação clara em cada fase da gestação</span></li>
          </ul>
          <a class="btn btn--outline" href="/contato"><span class="btn__label">Começar meu pré-natal <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
        </div>
      </article></li>

      <li class="rise rise--right" id="area-03" data-d="3"><article class="tri__card" data-glow>
        <figure class="tri__media">
          <img src="assets/img/hero-acolhimento.webp" alt="Dra. Marina Mariz apoiando uma gestante durante o trabalho de parto" width="1200" height="1600" loading="lazy" decoding="async">
        </figure>
        <div class="tri__body">
          <h3>Gestação de Alto Risco</h3>
          <p>Hipertensão, diabetes gestacional, gemelaridade, trombofilias, histórico de perdas e outras condições podem exigir um <strong>acompanhamento mais próximo</strong>. Alto risco não significa viver em estado de alerta permanente, mas ter <strong>planejamento, vigilância e condutas bem definidas</strong>.</p>
          <ul class="tri__points">
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Protocolos baseados nas melhores evidências</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Acompanhamento individualizado da mãe e do bebê</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Planejamento antecipado das possíveis condutas</span></li>
          </ul>
          <a class="btn btn--outline" href="/contato"><span class="btn__label">Quero acompanhamento <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
        </div>
      </article></li>

      <li class="rise rise--left" id="area-04" data-d="4"><article class="tri__card" data-glow>
        <figure class="tri__media">
          <img src="assets/img/cirurgia.webp" alt="Dra. Marina Mariz em procedimento no centro cirúrgico" width="1200" height="1600" loading="lazy" decoding="async">
        </figure>
        <div class="tri__body">
          <h3>Medicina Fetal</h3>
          <p>A medicina fetal acompanha o <strong>desenvolvimento do bebê</strong> com exames e avaliações capazes de identificar alterações, estimar riscos e orientar decisões durante a gestação. <strong>Informação precisa</strong> para compreender o que está acontecendo e qual deve ser o próximo passo.</p>
          <ul class="tri__points">
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Avaliações especializadas</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Rastreamento e acompanhamento do desenvolvimento fetal</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Resultados explicados com clareza e contexto</span></li>
          </ul>
          <a class="btn btn--outline" href="/contato"><span class="btn__label">Agendar minha consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
        </div>
      </article></li>

      <li class="rise" id="area-05" data-d="5"><article class="tri__card" data-glow>
        <figure class="tri__media">
          <img src="assets/img/pos-parto.webp" alt="Família reunida com o recém-nascido no quarto da maternidade" width="1066" height="1600" loading="lazy" decoding="async">
        </figure>
        <div class="tri__body">
          <h3>Assistência Personalizada</h3>
          <p><strong>Do positivo ao pós-parto</strong>, a paciente é acompanhada de forma contínua, com orientação, apoio e condução clínica em cada fase da gestação, do nascimento e da recuperação.</p>
          <ul class="tri__points">
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Planejamento das possibilidades de parto</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Decisões compartilhadas e baseadas em evidências</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Acompanhamento durante o trabalho de parto e nascimento</span></li>
          </ul>
          <a class="btn btn--outline" href="/contato"><span class="btn__label">Conhecer a assistência <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
        </div>
      </article></li>

      <li class="rise rise--right" id="area-06" data-d="6"><article class="tri__card" data-glow>
        <figure class="tri__media">
          <img src="assets/img/abraco.webp" alt="Dra. Marina Mariz abraçando uma mulher em trabalho de parto" width="1066" height="1600" loading="lazy" decoding="async">
        </figure>
        <div class="tri__body">
          <h3>Parto Humanizado</h3>
          <p>Humanizar o cuidado não é defender uma única forma de parir. É garantir que a mulher seja <strong>respeitada, informada e incluída nas decisões</strong>, independentemente da via de parto ou das intervenções que possam ser necessárias.</p>
          <ul class="tri__points">
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Respeito às escolhas, limites e preferências da mulher</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Informações claras sobre condutas, possibilidades e alternativas</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Autonomia preservada sem perder de vista a segurança clínica</span></li>
          </ul>
          <a class="btn btn--outline" href="/contato"><span class="btn__label">Quero saber mais <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
        </div>
      </article></li>
    </ol>

  </div>
</section>
@endsection

@section('pos-conteudo')
<!-- ===== partial: layouts/partials/cta — texto de Especialidades.md, seção 3 ===== -->
<section class="scene cta scheme-04" id="cta" data-scene data-tone="dark" aria-labelledby="cta-titulo">
  <div class="scene__body">
    <h2 class="t-h2 rise" id="cta-titulo">Pronta para dar<br>o próximo passo?</h2>
    <p class="lead rise" data-d="1"><span>Seja qual for a fase, o primeiro passo é o mesmo: uma consulta sem pressa, com espaço para falar abertamente sobre sua história, antes de propor qualquer conduta.</span></p>
    <div class="cta__action rise rise--zoom" data-d="2"><a class="btn btn--primary" href="/contato"><span class="btn__label">Agendar consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a></div>
  </div>
</section>
@endsection

@section('pos-rodape')
@include('site.partials.barra')
@endsection
