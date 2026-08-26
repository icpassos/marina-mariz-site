@extends('layouts.site', [
    'titulo' => 'Newsletter — Dra. Marina Mariz',
    'descricao' => 'A newsletter da Dra. Marina Mariz: o que sai no blog, no podcast e nos materiais gratuitos chega até você por e-mail. Assine em um minuto.',
    'canonical' => 'https://dramarinamariz.com.br/newsletter',
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/maos.webp',
])

{{--
    Pagina de apoio da newsletter. O caminho ate aqui e o link do rodape:
    quem ja esta lendo o blog ou baixando material assina pelos blocos que
    fecham aquelas paginas. Esta existe para quem nunca entrou em nenhuma
    delas e precisa saber que a newsletter existe antes de deixar o e-mail.
--}}

@section('conteudo')

<!-- ===== cena 1 — herói ===== -->
{{-- textos: escritos aqui; ainda nao ha Escopo das Paginas/Newsletter.md no vault --}}
<section class="scene nwh scheme-04" id="newsletter" data-scene data-tone="dark" aria-labelledby="news-titulo">
  <div class="nwh__halo" aria-hidden="true"></div>
  <div class="nwh__grain" aria-hidden="true"></div>
  <div class="scene__body">
    <div>
      <span class="eyebrow rise">Newsletter</span>
      <h1 class="t-h1 rise" data-d="1" data-split id="news-titulo">Conteúdo<br>de verdade,<br>direto no seu e-mail</h1>
      <p class="nwh__lead rise" data-d="2">O que sai no blog, no podcast e nos materiais gratuitos chega<br>até você por e-mail — <strong>escrito por quem acompanha gestação todos os dias.</strong></p>
      <div class="nwh__actions rise" data-d="3">
        <a class="btn btn--primary" href="#assinar"><span class="btn__label">Quero assinar <i class="ph ph-arrow-down" aria-hidden="true"></i></span></a>
        <a class="btn btn--outline" href="/blog"><span class="btn__label">Ver o blog <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a>
      </div>
    </div>

    <!-- decoração: o formato do e-mail, sem prometer assunto nenhum -->
    <div class="nwh__carta rise rise--right" data-d="2" aria-hidden="true">
      <div class="nwh__carta-de">
        <svg viewBox="0 0 45.5 45.43" role="presentation"><use href="#simbolo"></use></svg>
        <span><strong>Dra. Marina Mariz</strong><span>direto no seu e-mail</span></span>
      </div>
      <p class="nwh__carta-assunto">O que saiu de novo</p>
      <div class="nwh__carta-corpo"><span></span><span></span><span></span><span></span></div>
      <span class="nwh__carta-pe">Ler <i class="ph ph-arrow-up-right"></i></span>
    </div>
  </div>
</section>

<!-- ===== cena 2 — o que chega ===== -->
<section class="scene nwo scheme-05" id="o-que-chega" data-scene data-tone="light" aria-labelledby="chega-titulo">
  <div class="scene__body">
    <div class="nwo__aside">
      <span class="eyebrow rise">O que chega</span>
      <h2 class="t-h2 rise" data-d="1" id="chega-titulo">Uma seleção curta,<br>não uma enxurrada</h2>
      <p class="rise" data-d="2">É o <strong>resumo do que foi publicado, com o link para ler quando você tiver tempo</strong>.</p>
    </div>

    <ul class="nwo__list">
      <li class="nwo__item rise" data-d="1">
        <span class="nwo__ico"><i class="ph ph-file-text" aria-hidden="true"></i></span>
        <h3>O que saiu no blog</h3>
        <p>Textos sobre gestação, parto e puerpério, com o resumo antes do link — dá para decidir se vale a leitura sem sair do e-mail.</p>
      </li>
      <li class="nwo__item rise" data-d="2">
        <span class="nwo__ico"><i class="ph ph-microphone-stage" aria-hidden="true"></i></span>
        <h3>Os episódios do Sem Neura</h3>
        <p>Episódio novo, você fica sabendo sem precisar procurar. Com o assunto da conversa e onde ouvir.</p>
      </li>
      <li class="nwo__item rise" data-d="3">
        <span class="nwo__ico"><i class="ph ph-download-simple" aria-hidden="true"></i></span>
        <h3>Materiais gratuitos</h3>
        <p>Guias e checklists novos chegam aqui primeiro, com o link direto para baixar.</p>
      </li>
      <li class="nwo__item rise" data-d="4">
        <span class="nwo__ico"><i class="ph ph-calendar-blank" aria-hidden="true"></i></span>
        <h3>Cursos, eventos e turmas</h3>
        <p>Data marcada, aviso com antecedência — a tempo de você se organizar para participar.</p>
      </li>
    </ul>
  </div>
</section>

<!-- ===== cena 3 — o combinado ===== -->
<!-- LGPD: consentimento, finalidade e descadastro seguem Política de Privacidade e Painel/04 -->
<section class="scene nwp scheme-04" id="o-combinado" data-scene data-tone="dark" aria-labelledby="combinado-titulo">
  <div class="scene__body">
    <div class="nwp__head">
      <span class="eyebrow rise">O combinado</span>
      <h2 class="t-h2 rise" data-d="1" id="combinado-titulo">Sem pegadinha, como<br>tudo por aqui</h2>
    </div>

    <ul class="nwp__grid">
      <li class="nwp__item rise" data-glow data-d="1">
        <i class="ph ph-envelope-simple" aria-hidden="true"></i>
        <strong>Só quando tem novidade</strong>
        <p>Não existe sequência automática, nem e-mail de lembrete, nem promoção disfarçada de conteúdo. Caixa de entrada não é vitrine.</p>
      </li>
      <li class="nwp__item rise" data-glow data-d="2">
        <i class="ph ph-shield-check" aria-hidden="true"></i>
        <strong>Seu e-mail fica aqui</strong>
        <p>Não vendemos, não trocamos e não repassamos a sua inscrição para ninguém. Ela serve para mandar a newsletter, e só.</p>
      </li>
      <li class="nwp__item rise" data-glow data-d="3">
        <i class="ph ph-prohibit" aria-hidden="true"></i>
        <strong>Sair é um clique</strong>
        <p>Todo e-mail traz o link de descadastro. Um clique, sem login e sem precisar justificar nada.</p>
      </li>
    </ul>

    <p class="nwp__nota rise" data-d="4"><i class="ph ph-info" aria-hidden="true"></i><span>Assinar é opcional e a caixa de consentimento vem desmarcada. O tratamento dos seus dados está descrito na <a href="/politica-de-privacidade">Política de Privacidade</a>.</span></p>
  </div>
</section>

<!-- ===== cena 4 — inscrição ===== -->
<!-- destino: Painel/04 — Newsletter · lista única; `origem` guarda de qual página veio -->
<section class="scene nwa scheme-05" id="assinar" data-scene data-tone="light" aria-labelledby="assinar-titulo">
  <div class="scene__body">
    <span class="eyebrow rise">Assinar</span>
    <h2 class="t-h2 rise" data-d="1" id="assinar-titulo">Deixe seu nome e e-mail</h2>
    <p class="nwa__lead rise" data-d="2">A próxima edição chega assim que sair.<br>Se não for para você, o descadastro está no rodapé de todo e-mail.</p>

    {{-- Mesmo bloco que fecha as paginas de Educacao: lista unica, `origem`
         guarda de qual pagina veio a inscricao. --}}
    @include('site.educacao.partes.newsletter', [
        'chave' => 'newsletter-pagina',
        'origem' => 'newsletter',
        'prefixo' => 'nw',
        'rotulo' => 'Assinar newsletter',
    ])
  </div>
</section>
@endsection

@section('pos-rodape')
@include('site.partials.barra')
@endsection
