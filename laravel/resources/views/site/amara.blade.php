@extends('layouts.site', [
    'titulo' => 'Amara — Dra. Marina Mariz',
    'descricao' => 'A Amara é o coletivo de obstetrícia cofundado pela Dra. Marina Mariz: obstetras, enfermeiras, doulas e consultoras com conduta alinhada e backup confiável em cada nascimento.',
    'canonical' => 'https://dramarinamariz.com.br/amara',
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/amara/19.webp',
    'atual' => 'amara',
])

@section('conteudo')
<!-- ===== cena 1 — herói ===== -->
<!-- textos: Escopo das Páginas/Amara.md, seção 1 -->
<section class="scene amh scheme-04" id="coletivo" data-scene data-tone="dark" aria-labelledby="amara-titulo">
  <div class="amh__halo a" aria-hidden="true"></div>
  <div class="amh__halo b" aria-hidden="true"></div>
  <div class="amh__grain" aria-hidden="true"></div>
  <div class="scene__body">
    <svg class="amh__logo rise" data-d="1" viewBox="0 0 659.72 87.45" role="img" aria-label="Amara"><use href="#amara-logo"></use></svg>
    <h1 class="t-h1 rise" data-d="2" data-split id="amara-titulo">A força do coletivo</h1>
    <p class="amh__copy rise" data-d="3">A Dra. Marina Mariz é uma das <strong>fundadoras da Amara</strong>: um núcleo de obstetrícia formado por <strong>mulheres, mães e médicas obstetras</strong> que compartilham a mesma filosofia — <strong>ciência e acolhimento</strong> em cada nascimento.</p>
    <div class="amh__actions rise" data-d="4">
      <a class="btn btn--primary" href="/contato"><span class="btn__label">Agendar consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
      <a class="btn btn--outline" href="#coletivo-equipe"><span class="btn__label">Quem forma o coletivo <i class="ph ph-arrow-down" aria-hidden="true"></i></span></a>
    </div>

    <div class="amh__fan rise rise--zoom" data-d="5">
      <figure><img src="assets/img/amara/1.webp" alt="" width="675" height="900" loading="lazy" decoding="async"></figure>
      <figure><img src="assets/img/amara/9.webp" alt="" width="675" height="900" loading="lazy" decoding="async"></figure>
      <figure><img src="assets/img/amara/19.webp" alt="Profissionais da Amara reunidas" width="642" height="900" fetchpriority="high"></figure>
      <figure><img src="assets/img/amara/12.webp" alt="" width="900" height="598" loading="lazy" decoding="async"></figure>
      <figure><img src="assets/img/amara/15.webp" alt="" width="599" height="900" loading="lazy" decoding="async"></figure>
      <svg class="amh__badge" viewBox="0 0 149.31 148.64" aria-hidden="true"><use href="#amara-badge"></use></svg>
    </div>
  </div>
</section>

<!-- ===== cena 2 — descrição ===== -->
<!-- textos: Escopo das Páginas/Amara.md, seção 2 -->
<section class="scene amd scheme-05" id="sistema" data-scene data-tone="light" aria-labelledby="sistema-titulo">
  <div class="scene__body">
    <div class="amd__grid">
      <h2 class="t-h2 rise" id="sistema-titulo">Mais do que uma equipe<br>Um sistema de cuidado</h2>
      <div class="amd__text">
        <p class="rise" data-d="1">A Amara é um <strong>coletivo multidisciplinar</strong> que reúne profissionais cuidadosamente selecionados pela Dra. Marina Mariz — unidos não apenas pela <strong>excelência técnica</strong>, mas por uma <strong>filosofia de prática compartilhada</strong>.</p>
        <p class="rise" data-d="2">Aqui, cada profissional conhece o trabalho dos demais. As decisões são tomadas <strong>em conjunto</strong>. A comunicação é <strong>fluida</strong>. E a mulher — sempre no centro — recebe um cuidado que é <strong>maior do que a soma das partes</strong>.</p>
      </div>
    </div>

    <ol class="amln" aria-label="O acompanhamento da Amara, do planejamento ao puerpério">
      <li class="amln__step rise" data-d="1">
        <span class="amln__dot" aria-hidden="true"><i class="ph ph-compass"></i></span>
        <span class="amln__fase">Antes</span>
        <h3>Tentando engravidar</h3>
        <p>Consulta preconcepcional, exames e ajustes de rotina — o corpo preparado antes do primeiro atraso.</p>
      </li>
      <li class="amln__step rise" data-d="2">
        <span class="amln__dot" aria-hidden="true"><i class="ph ph-heart"></i></span>
        <span class="amln__fase">1º trimestre</span>
        <h3>O positivo</h3>
        <p>Confirmação, primeiro ultrassom e o pré-natal desenhado com você — não um protocolo pronto.</p>
      </li>
      <li class="amln__step rise" data-d="3">
        <span class="amln__dot" aria-hidden="true"><i class="ph ph-stethoscope"></i></span>
        <span class="amln__fase">2º trimestre</span>
        <h3>A gestação em curso</h3>
        <p>Consultas no seu ritmo, exames de rotina e as dúvidas respondidas também fora do consultório.</p>
      </li>
      <li class="amln__step rise" data-d="4">
        <span class="amln__dot" aria-hidden="true"><i class="ph ph-clock-countdown"></i></span>
        <span class="amln__fase">3º trimestre</span>
        <h3>Preparo para o parto</h3>
        <p>Plano de parto escrito a quatro mãos e compartilhado com todo o coletivo, semanas antes da hora.</p>
      </li>
      <li class="amln__step rise" data-d="5">
        <span class="amln__dot" aria-hidden="true"><i class="ph ph-baby"></i></span>
        <span class="amln__fase">O nascimento</span>
        <h3>Parto</h3>
        <p>Quem entra na sala já acompanhou tudo: obstetra, enfermeira e doula na mesma conduta.</p>
      </li>
      <li class="amln__step rise" data-d="6">
        <span class="amln__dot" aria-hidden="true"><i class="ph ph-hand-heart"></i></span>
        <span class="amln__fase">Depois</span>
        <h3>Puerpério</h3>
        <p>Amamentação, recuperação e saúde mental acompanhadas nas semanas em que a rotina inteira muda.</p>
      </li>
    </ol>

    <div class="amd__foot rise" data-d="3">
      <p class="amd__cue">Cada nascimento é <strong>conduzido por quem já acompanhou a gestação inteira</strong> — de antes do teste positivo às semanas seguintes ao parto.</p>
      <a class="btn btn--primary" href="/contato"><span class="btn__label">Quero acompanhamento <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
    </div>
  </div>
</section>

<!-- ===== cena 3 — a citação ===== -->
<!-- texto: Escopo das Páginas/Amara.md, seção 1 (pull quote) -->
<section class="scene amq scheme-04" id="rede" data-scene data-tone="dark">
  <div class="amq__glow" aria-hidden="true"></div>
  <div class="scene__body">
    <svg class="amq__mark rise rise--zoom" viewBox="46 46 57.4 57.4" aria-hidden="true"><use href="#amara-mark"></use></svg>
    <blockquote class="rise" data-d="1"><p>Fazer parte da Amara significa oferecer às pacientes uma <b>rede de segurança robusta e confiável</b>. Um sistema de backup com profissionais que seguem uma mesma conduta, garantindo que, em qualquer eventualidade, a paciente seja amparada por uma equipe que <b>conhece sua história</b>, respeita seus valores e mantém uma linha de cuidado em comum.</p></blockquote>
    <p class="amq__by rise" data-d="2">Dra. Marina Mariz</p>
  </div>
</section>

<!-- ===== cena 4 — quem forma o coletivo ===== -->
<!-- textos: Escopo das Páginas/Amara.md, seção 3 -->
<section class="scene amt scheme-04" id="coletivo-equipe" data-scene data-tone="dark" aria-labelledby="equipe-titulo">
  <div class="scene__body">
    <header class="sec__head sec__head--mid">
      <h2 class="t-h2 rise" id="equipe-titulo">Quem forma o coletivo</h2>
      <p class="lead rise" data-d="2">Quatro frentes que se revezam e se cobrem. <strong>Nenhuma delas trabalha isolada</strong> — e nenhuma delas chega sem saber a sua história.</p>
    </header>

    <ul class="amt__grid">
      <li class="rise rise--left" data-d="1"><article class="amt__card">
        <img src="assets/img/amara/20.webp" alt="Equipe acompanhando a família no quarto da maternidade" width="932" height="1400" loading="lazy" decoding="async">
        <div class="amt__tag">
          <strong>Obstetras</strong>
          <span>Quatro médicas que dividem plantão, conduta e a mesma leitura de evidência.</span>
        </div>
      </article></li>

      <li class="rise" data-d="2"><article class="amt__card">
        <img src="assets/img/amara/17.webp" alt="Equipe da Amara com a mãe e o recém-nascido" width="931" height="1400" loading="lazy" decoding="async">
        <div class="amt__tag">
          <strong>Enfermeiras obstétricas</strong>
          <span>Presença técnica no trabalho de parto, do primeiro sinal ao pós-imediato.</span>
        </div>
      </article></li>

      <li class="rise" data-d="3"><article class="amt__card">
        <img src="assets/img/amara/15.webp" alt="Doula apoiando uma mulher em trabalho de parto" width="599" height="900" loading="lazy" decoding="async">
        <div class="amt__tag">
          <strong>Doulas</strong>
          <span>Suporte contínuo à mulher e a quem a acompanha, sem interrupção de turno.</span>
        </div>
      </article></li>

      <li class="rise rise--right" data-d="4"><article class="amt__card">
        <img src="assets/img/amara/12.webp" alt="Família com o recém-nascido no quarto da maternidade" width="900" height="598" loading="lazy" decoding="async">
        <div class="amt__tag">
          <strong>Consultoras</strong>
          <span>Apoio especializado nas semanas seguintes ao parto, quando a rotina muda.</span>
        </div>
      </article></li>
    </ul>

    <div class="amt__foot rise" data-d="5">
      <p class="amt__note">O núcleo reúne <strong>quatro obstetras</strong>, além de enfermeiras obstétricas,<br>doulas e consultoras — todas escolhidas pela Dra. Marina Mariz.</p>
      <a class="btn btn--primary" href="/contato"><span class="btn__label">Marcar minha consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
    </div>
  </div>
</section>

<!-- ===== cena 5 — filosofia ===== -->
<!-- textos: Escopo das Páginas/Amara.md, seção 4 -->
<section class="scene amp scheme-05" id="filosofia" data-scene data-tone="light" aria-labelledby="filosofia-titulo">
  <div class="scene__body">
    <header class="sec__head sec__head--mid">
      <h2 class="t-h2 rise" id="filosofia-titulo">Uma filosofia compartilhada</h2>
      <p class="lead rise" data-d="2">Quatro compromissos que valem para todas as <strong>profissionais do coletivo</strong> — e que <strong>a paciente sente na prática</strong>, não no papel.</p>
    </header>

    <ol class="amp__grid">
      <li class="rise rise--left" data-d="1"><article class="amp__item">
        <span class="amp__ico"><i class="ph ph-shield-check" aria-hidden="true"></i></span>
        <div>
          <h3>Backup confiável</h3>
          <p>Se a Marina não puder estar, quem chega já conhece a sua história, o seu plano de parto e as suas escolhas. <strong>Ninguém começa do zero.</strong></p>
        </div>
      </article></li>

      <li class="rise rise--right" data-d="2"><article class="amp__item">
        <span class="amp__ico"><i class="ph ph-compass" aria-hidden="true"></i></span>
        <div>
          <h3>Conduta alinhada</h3>
          <p>As profissionais partilham os mesmos protocolos e a mesma leitura de evidência. <strong>Você não recebe orientações que se contradizem</strong> de uma consulta para a outra.</p>
        </div>
      </article></li>

      <li class="rise rise--left" data-d="3"><article class="amp__item">
        <span class="amp__ico"><i class="ph ph-chats-circle" aria-hidden="true"></i></span>
        <div>
          <h3>Comunicação fluida</h3>
          <p>Os casos são discutidos entre a equipe. <strong>O que uma observa chega às outras</strong> antes da próxima consulta — sem depender de você repetir tudo.</p>
        </div>
      </article></li>

      <li class="rise rise--right" data-d="4"><article class="amp__item">
        <span class="amp__ico"><i class="ph ph-hand-heart" aria-hidden="true"></i></span>
        <div>
          <h3>Mulher no centro</h3>
          <p>A decisão final é sempre sua. O papel do coletivo é dar <strong>informação, contexto e segurança</strong> para que ela seja tomada com calma.</p>
        </div>
      </article></li>
    </ol>
  </div>
</section>
@endsection

@section('pos-conteudo')
<!-- ===== partial: layouts/partials/cta — texto de Amara.md, seção 5 ===== -->
<section class="scene cta scheme-04" id="cta" data-scene data-tone="dark" aria-labelledby="cta-titulo">
  <div class="scene__body">
    <h2 class="t-h2 rise" id="cta-titulo">Conheça a equipe<br>que vai cuidar de você</h2>
    <p class="lead rise" data-d="1"><span>Agende uma consulta com a Dra. Marina Mariz e<br>conheça de perto o coletivo que acompanha cada gestação.</span></p>
    <div class="cta__action rise rise--zoom" data-d="2"><a class="btn btn--primary" href="/contato"><span class="btn__label">Agendar consulta <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a></div>
  </div>
</section>
@endsection

@section('pos-rodape')
@include('site.partials.barra')
@endsection
