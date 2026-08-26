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
      <h1 class="t-h1 rise" data-d="1" data-split id="esp-titulo">Cuidado especializado<br>em cada fase da jornada</h1>
      <p class="lead rise" data-d="2">Ginecologia, obstetrícia e medicina fetal num <strong>acompanhamento contínuo</strong> — do plano de engravidar ao retorno depois do parto.</p>
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
        <li><a href="#area-01"><span class="esph__n">01</span><span>Gestação de Alto Risco</span><i class="ph ph-arrow-right" aria-hidden="true"></i></a></li>
        <li><a href="#area-02"><span class="esph__n">02</span><span>Medicina Fetal e Ultrassonografia</span><i class="ph ph-arrow-right" aria-hidden="true"></i></a></li>
        <li><a href="#area-03"><span class="esph__n">03</span><span>Parto Humanizado</span><i class="ph ph-arrow-right" aria-hidden="true"></i></a></li>
        <li><a href="#area-04"><span class="esph__n">04</span><span>Puerpério e Pós-parto</span><i class="ph ph-arrow-right" aria-hidden="true"></i></a></li>
        <li><a href="#area-05"><span class="esph__n">05</span><span>Pré-concepção e Planejamento Familiar</span><i class="ph ph-arrow-right" aria-hidden="true"></i></a></li>
        <li><a href="#area-06"><span class="esph__n">06</span><span>Saúde da Mulher</span><i class="ph ph-arrow-right" aria-hidden="true"></i></a></li>
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
      <span class="marquee__item">Alto risco <i>·</i> Medicina fetal <i>·</i> Parto humanizado <i>·</i> Puerpério <i>·</i> Pré-concepção <i>·</i> Saúde da mulher <i>·</i></span>
      <span class="marquee__item">Alto risco <i>·</i> Medicina fetal <i>·</i> Parto humanizado <i>·</i> Puerpério <i>·</i> Pré-concepção <i>·</i> Saúde da mulher <i>·</i></span>
    </div>
  </div>
</section>

<!-- ===== selo de transição ===== -->
<div class="seal-band seal-band--rule scheme-05" data-scene data-tone="light">
  <div class="seal-band__inner seal-band__inner--center">
    <svg class="seal-band__seal rise rise--zoom" viewBox="0 0 45.5 45.43" aria-hidden="true"><use href="#simbolo"></use></svg>
    <p class="seal-band__text rise" data-d="1">Do plano de engravidar ao retorno depois do parto — <b>a mesma médica</b>, do começo ao fim</p>
  </div>
</div>

<!-- ===== cena 2 — listagem das especialidades ===== -->
<!-- textos: Escopo das Páginas/Especialidades.md, seção 2 -->
<section class="scene tri scheme-05" id="areas" data-scene data-tone="light" aria-labelledby="areas-titulo">
  <div class="scene__body">
    <header class="sec__head sec__head--mid">
      <h2 class="t-h2 rise" data-d="1" id="areas-titulo">Seis frentes de cuidado</h2>
      <p class="lead rise" data-d="2">Cada fase pede uma <strong>condução própria</strong>. O que não muda é o <strong>rigor técnico</strong> e o direito de <strong>entender cada decisão</strong> antes que ela seja tomada.</p>
    </header>

    <ol class="tri__grid">
      <li class="rise rise--left" id="area-01" data-d="1"><article class="tri__card" data-glow>
        <figure class="tri__media">
          <img src="assets/img/hero-acolhimento.webp" alt="Dra. Marina Mariz apoiando uma gestante durante o trabalho de parto" width="1200" height="1600" loading="lazy" decoding="async">
        </figure>
        <div class="tri__body">
          <h3>Gestação de Alto Risco</h3>
          <p>Hipertensão, diabetes gestacional, gemelaridade, trombofilia, histórico de perda — cada cenário pede um plano próprio. O acompanhamento fica <strong>mais frequente</strong> e o plano de parto é construído com antecedência, para que nenhuma decisão precise ser tomada no susto.</p>
          <ul class="tri__points">
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Protocolos baseados nas evidências mais atuais</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Consultas em intervalo mais curto, com canal direto entre elas</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Plano de parto definido antes de a urgência aparecer</span></li>
          </ul>
          <a class="btn btn--outline" href="/contato"><span class="btn__label">Agendar avaliação <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
        </div>
      </article></li>

      <li class="rise" id="area-02" data-d="2"><article class="tri__card" data-glow>
        <figure class="tri__media">
          <img src="assets/img/cirurgia.webp" alt="Dra. Marina Mariz em procedimento no centro cirúrgico" width="1200" height="1600" loading="lazy" decoding="async">
        </figure>
        <div class="tri__body">
          <h3>Medicina Fetal e Ultrassonografia</h3>
          <p>Morfológico de primeiro e de segundo trimestre, doppler, curva de crescimento e rastreio de malformações. O exame é feito e <strong>explicado na mesma consulta</strong>: você sai sabendo o que foi visto, o que aquilo significa e o que vem depois.</p>
          <ul class="tri__points">
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Morfológico de 1º e de 2º trimestre</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Doppler e acompanhamento do crescimento fetal</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Resultado explicado na hora, em linguagem clara</span></li>
          </ul>
          <a class="btn btn--outline" href="/contato"><span class="btn__label">Agendar exame <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
        </div>
      </article></li>

      <li class="rise rise--right" id="area-03" data-d="3"><article class="tri__card" data-glow>
        <figure class="tri__media">
          <img src="assets/img/abraco.webp" alt="Dra. Marina Mariz abraçando uma mulher em trabalho de parto" width="1066" height="1600" loading="lazy" decoding="async">
        </figure>
        <div class="tri__body">
          <h3>Parto Humanizado</h3>
          <p>Humanizado não é um tipo de parto — é a <strong>forma de conduzir qualquer um deles</strong>. Cesárea ou normal, a via é decidida por <strong>indicação clínica</strong>, nunca por agenda, e cada etapa é explicada antes de acontecer.</p>
          <ul class="tri__points">
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Decisões partilhadas em cada etapa</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Via de parto definida por indicação, não por conveniência</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Equipe presente do trabalho de parto ao pós-imediato</span></li>
          </ul>
          <a class="btn btn--outline" href="/contato"><span class="btn__label">Conversar sobre o parto <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
        </div>
      </article></li>

      <li class="rise rise--left" id="area-04" data-d="4"><article class="tri__card" data-glow>
        <figure class="tri__media">
          <img src="assets/img/pos-parto.webp" alt="Família reunida com o recém-nascido no quarto da maternidade" width="1066" height="1600" loading="lazy" decoding="async">
        </figure>
        <div class="tri__body">
          <h3>Puerpério e Pós-parto</h3>
          <p>O quarto trimestre costuma ser o mais silencioso e o mais difícil. Amamentação, cicatrização, sono, humor, retorno da libido: tudo isso é <strong>assunto de consulta</strong>. O acompanhamento continua depois que o bebê nasce.</p>
          <ul class="tri__points">
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Retorno precoce, sem esperar a sexta semana</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Apoio à amamentação e à recuperação física</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Rastreio de depressão pós-parto</span></li>
          </ul>
          <a class="btn btn--outline" href="/contato"><span class="btn__label">Agendar retorno <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
        </div>
      </article></li>

      <li class="rise" id="area-05" data-d="5"><article class="tri__card" data-glow>
        <figure class="tri__media">
          <img src="assets/img/planejamento.webp" alt="Dra. Marina Mariz em ambiente de consulta, fora do hospital" width="1066" height="1600" loading="lazy" decoding="async">
        </figure>
        <div class="tri__body">
          <h3>Pré-concepção e Planejamento Familiar</h3>
          <p>O melhor momento de cuidar de uma gestação é <strong>antes dela existir</strong>: exames, revisão de medicações, suplementação e controle de doenças crônicas. E quando a escolha é não engravidar agora, a <strong>contracepção que cabe na sua vida</strong>.</p>
          <ul class="tri__points">
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Exames e suplementação antes de engravidar</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Revisão de medicações e de condições crônicas</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Método contraceptivo com prós e contras na mesa</span></li>
          </ul>
          <a class="btn btn--outline" href="/contato"><span class="btn__label">Planejar com a Marina <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
        </div>
      </article></li>

      <li class="rise rise--right" id="area-06" data-d="6"><article class="tri__card" data-glow>
        <figure class="tri__media">
          <img src="assets/img/saude-da-mulher.webp" alt="Quatro mulheres reunidas em ambiente iluminado" width="1141" height="1600" loading="lazy" decoding="async">
        </figure>
        <div class="tri__body">
          <h3>Saúde da Mulher</h3>
          <p>Ginecologia geral da adolescência ao climatério: consulta de rotina, <strong>rastreio de câncer de colo e mama</strong>, investigação de cólicas e sangramentos fora do padrão, saúde sexual e terapia hormonal quando há indicação.</p>
          <ul class="tri__points">
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Rotina e rastreios em dia</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Investigação de cólicas, sangramentos e dor pélvica</span></li>
            <li><i class="ph ph-check-circle" aria-hidden="true"></i><span>Climatério e terapia hormonal com indicação criteriosa</span></li>
          </ul>
          <a class="btn btn--outline" href="/contato"><span class="btn__label">Agendar consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
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
    <p class="lead rise" data-d="1"><span>Seja qual for a fase, o primeiro passo é o mesmo: uma consulta com tempo de sobra para ouvir a sua história antes de propor qualquer conduta.</span></p>
    <div class="cta__action rise rise--zoom" data-d="2"><a class="btn btn--primary" href="/contato"><span class="btn__label">Agendar consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a></div>
  </div>
</section>
@endsection

@section('pos-rodape')
@include('site.partials.barra')
@endsection
