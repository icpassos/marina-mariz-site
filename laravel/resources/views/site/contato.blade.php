@extends('layouts.site', [
    'titulo' => 'Contato — Dra. Marina Mariz',
    'descricao' => 'Fale com a Dra. Marina Mariz: WhatsApp, telefone, e-mail e formulário. Consultório na R. Cláudio Manoel, 48 — Funcionários, Belo Horizonte.',
    'canonical' => 'https://dramarinamariz.com.br/contato',
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/consultorio.webp',
    'atual' => 'contato',
])

@section('jsonld')
{{-- Bloco literal: "@context" e "@type" sao nomes de diretiva do Blade. --}}
@verbatim
<!-- dados estruturados do consultório — Painel/03 — Contatos e Dados
     blade: os valores vêm do singleton de contato, nunca escritos na página -->
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"MedicalClinic","name":"Dra. Marina Mariz — Ginecologia e Obstetrícia",
"url":"https://dramarinamariz.com.br/contato","email":"contato@dramarinamariz.com.br","telephone":"+55-31-3090-2320",
"address":{"@type":"PostalAddress","streetAddress":"R. Cláudio Manoel, 48 — Sala 1201","addressLocality":"Belo Horizonte","addressRegion":"MG","postalCode":"30140-100","addressCountry":"BR"},
"openingHoursSpecification":{"@type":"OpeningHoursSpecification","dayOfWeek":["Monday","Tuesday","Wednesday","Thursday","Friday"],"opens":"08:00","closes":"18:00"},
"medicalSpecialty":["Obstetric","Gynecologic"]}
</script>
@endverbatim
@endsection

@section('conteudo')
<!-- ===== cena 1 — herói: o convite e os três canais diretos ===== -->
<!-- textos: Escopo das Páginas/Contato.md, seção 1 · dados: Painel/03 — Contatos e Dados -->
@php $dados = \App\Services\Site::dados(); @endphp
<section class="scene cth scheme-04" id="contato" data-scene data-tone="dark" aria-labelledby="contato-titulo">
  <div class="scene__bg" aria-hidden="true"><img src="assets/img/consultorio.webp" alt="" width="1066" height="1600" data-depth="0.07" fetchpriority="high"></div>
  <div class="scene__veil cth__veil" aria-hidden="true"></div>
  <div class="cth__grain" aria-hidden="true"></div>
  <div class="scene__body">
    <span class="eyebrow rise">Contato</span>
    <h1 class="t-h1 rise" data-d="1" data-split id="contato-titulo">O primeiro passo<br>é uma conversa</h1>
    <p class="cth__lead rise" data-d="2">Seja para <strong>agendar uma consulta</strong>, tirar uma dúvida<br>ou simplesmente conhecer melhor o trabalho. Estou aqui.<br>Entre em contato pelo canal que for mais confortável para você.</p>

    <!-- blade: os três valores vêm do singleton de contato -->
    <div class="cth__atalhos rise" data-d="3">
      <a class="atalho" href="{{ \App\Services\Site::linkWhatsapp() }}" target="_blank" rel="noopener" data-glow>
        <span class="atalho__ico"><i class="ph ph-whatsapp-logo" aria-hidden="true"></i></span>
        <span class="atalho__txt"><strong>WhatsApp</strong><span>{{ \App\Services\Site::whatsappFormatado() }}</span></span>
        <i class="ph ph-arrow-up-right atalho__seta" aria-hidden="true"></i>
      </a>
      <a class="atalho" href="{{ \App\Services\Site::linkTel() }}" data-glow>
        <span class="atalho__ico"><i class="ph ph-phone" aria-hidden="true"></i></span>
        <span class="atalho__txt"><strong>Telefone</strong><span>{{ \App\Services\Site::telefoneFormatado() }}</span></span>
        <i class="ph ph-arrow-up-right atalho__seta" aria-hidden="true"></i>
      </a>
      <a class="atalho" href="mailto:{{ $dados->email }}" data-glow>
        <span class="atalho__ico"><i class="ph ph-envelope-simple" aria-hidden="true"></i></span>
        <span class="atalho__txt"><strong>E-mail</strong><span>{{ $dados->email }}</span></span>
        <i class="ph ph-arrow-up-right atalho__seta" aria-hidden="true"></i>
      </a>
    </div>

    <p class="cth__status rise" data-d="4"><span class="pulse" aria-hidden="true"></span><span>Segunda a sexta, 8h às 18h</span><span>resposta em até 1 dia útil</span></p>
  </div>
</section>

<!-- ===== cena 2 — formulário, com o caminho da mensagem ao lado ===== -->
<!-- textos: Escopo das Páginas/Contato.md, seção 2 · destino: Painel/04 — Leads, Newsletter e Mensagens
     estados de envio, campo inválido e erro: estados.html, seção Formulário de contato -->
<section class="scene ctf scheme-05" id="mensagem" data-scene data-tone="light" aria-labelledby="mensagem-titulo">
  <div class="scene__body">
    <div class="ctf__lado">
      <h2 class="t-h2 rise" id="mensagem-titulo">Prefere escrever?</h2>
      <p class="ctf__lead rise" data-d="1">Conte o que precisa em poucas linhas. A mensagem chega direto na caixa da equipe. E ninguém some com ela.</p>

      <ol class="ctf__passos rise" data-d="2">
        <li class="ctf__passo">
          <span class="ctf__n">01</span>
          <div><strong>Você escreve</strong><p>Nome, e-mail e o assunto. O WhatsApp é opcional, mas acelera a resposta.</p></div>
        </li>
        <li class="ctf__passo">
          <span class="ctf__n">02</span>
          <div><strong>A equipe lê</strong><p>Toda mensagem cai na caixa do painel e dispara um aviso por e-mail para quem atende.</p></div>
        </li>
        <li class="ctf__passo">
          <span class="ctf__n">03</span>
          <div><strong>Respondemos em até 1 dia útil</strong><p>Se for urgente, o WhatsApp continua sendo o caminho mais rápido.</p></div>
        </li>
      </ol>

      <p class="ctf__aviso rise" data-d="3"><i class="ph ph-shield-check" aria-hidden="true"></i><span>Não envie sintomas, exames ou informações de saúde por aqui. Esse cuidado é da consulta.</span></p>
    </div>

@php
    // Erro so decora este formulario; a pagina pode ganhar outros.
    $chave = 'contato';
    $falhou = $errors->any() && old('form') === $chave;
    $erro = fn (string $campo): ?string => $falhou ? $errors->first($campo) : null;
    $primeiro = $falhou ? array_key_first($errors->messages()) : null;
@endphp
@if (session('form') === $chave)
    {{-- Enviada: o formulario sai de cena e da lugar ao recibo, com o
         WhatsApp para quem nao quer esperar (estados.html). --}}
    <div class="ctf__form rise rise--right" data-d="2" style="display:grid;gap:14px">
      <p class="bln__msg bln__msg--ok" role="status" tabindex="-1" autofocus><i class="ph ph-check-circle" aria-hidden="true"></i>Mensagem enviada. Respondemos em até 1 dia útil.</p>
      <a class="btn btn--outline" href="{{ \App\Services\Site::linkWhatsapp() }}" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-whatsapp-logo" aria-hidden="true"></i> Prefere falar agora? Chamar no WhatsApp</span></a>
    </div>
@else
    <form class="ctf__form rise rise--right" data-d="2" method="post" action="{{ route('formularios.contato') }}" novalidate>
      @csrf
      <input type="hidden" name="submission_id" value="{{ old('submission_id', \Illuminate\Support\Str::uuid()) }}">
      <input type="hidden" name="form" value="{{ $chave }}">
      <input type="hidden" name="origem" value="contato">
      <input type="text" name="website" class="bln__trap" tabindex="-1" autocomplete="off" aria-hidden="true">

      <p @class(['field', 'field--erro' => $erro('nome')])><label for="ct-nome">Nome completo</label><input id="ct-nome" name="nome" type="text" placeholder="Como podemos te chamar" autocomplete="name" required value="{{ old('nome') }}" @if ($erro('nome')) aria-invalid="true" aria-describedby="ct-nome-erro" @if ($primeiro === 'nome') autofocus @endif @endif>@if ($erro('nome'))<span class="field__erro" id="ct-nome-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('nome') }}</span>@endif</p>
      <div class="bln__row">
        <p @class(['field', 'field--erro' => $erro('email')])><label for="ct-email">E-mail</label><input id="ct-email" name="email" type="email" placeholder="seu@email.com" autocomplete="email" required value="{{ old('email') }}" @if ($erro('email')) aria-invalid="true" aria-describedby="ct-email-erro" @if ($primeiro === 'email') autofocus @endif @endif>@if ($erro('email'))<span class="field__erro" id="ct-email-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('email') }}</span>@endif</p>
        <p @class(['field', 'field--erro' => $erro('whatsapp')])><label for="ct-zap">WhatsApp <span class="field__opc">(opcional)</span></label><input id="ct-zap" name="whatsapp" type="tel" placeholder="(31) 90000-0000" autocomplete="tel" value="{{ old('whatsapp') }}" @if ($erro('whatsapp')) aria-invalid="true" aria-describedby="ct-zap-erro" @endif>@if ($erro('whatsapp'))<span class="field__erro" id="ct-zap-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('whatsapp') }}</span>@endif</p>
      </div>
      <p @class(['field', 'field--erro' => $erro('texto')])><label for="ct-msg">Mensagem</label><textarea id="ct-msg" name="texto" rows="5" placeholder="Escreva aqui o que você precisa" required @if ($erro('texto')) aria-invalid="true" aria-describedby="ct-msg-erro" @if ($primeiro === 'texto') autofocus @endif @endif>{{ old('texto') }}</textarea>@if ($erro('texto'))<span class="field__erro" id="ct-msg-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('texto') }}</span>@endif</p>

      <label class="bln__consent">
        <input type="checkbox" name="ciencia_politica" value="1" required @if ($erro('ciencia_politica')) aria-invalid="true" aria-describedby="ct-ciencia-erro" autofocus @endif>
        <span>Li e estou ciente da <a href="/politica-de-privacidade">Política de Privacidade</a></span>
@if ($erro('ciencia_politica'))
        <span class="field__erro" id="ct-ciencia-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('ciencia_politica') }}</span>
@endif
      </label>

      <div class="ctf__envio">
        <button class="btn btn--primary" type="submit"><span class="btn__label">Enviar mensagem <i class="ph ph-paper-plane-tilt" aria-hidden="true"></i></span></button>
        <span class="ctf__ou">ou</span>
        <a class="btn btn--outline" href="{{ \App\Services\Site::linkWhatsapp() }}" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-whatsapp-logo" aria-hidden="true"></i> Chamar no WhatsApp</span></a>
      </div>
    </form>
@endif
  </div>
</section>

<!-- ===== cena 3 — onde fica o consultório ===== -->
<!-- endereço e horário: Painel/03 — Contatos e Dados -->
<section class="scene ctm scheme-04" id="consultorio" data-scene data-tone="dark" aria-labelledby="consultorio-titulo">
  <div class="ctm__halo" aria-hidden="true"></div>
  <div class="scene__body">
    <figure class="ctm__shot rise rise--left">
      <img src="assets/img/planejamento.webp" alt="Sala de atendimento da Dra. Marina Mariz" width="1066" height="1600" loading="lazy" decoding="async">
      <figcaption><i class="ph ph-map-pin" aria-hidden="true"></i>Funcionários, Belo Horizonte</figcaption>
    </figure>

    <div class="ctm__dados">
      <span class="eyebrow rise">O consultório</span>
      <h2 class="t-h2 rise" data-d="1" id="consultorio-titulo">Nosso encontro<br>presencial.</h2>

      <ul class="ctm__lista rise" data-d="2">
        <li class="ctm__linha">
          <span class="ctm__ico"><i class="ph ph-map-pin" aria-hidden="true"></i></span>
          <div><strong>R. Cláudio Manoel, 48 — Sala 1201</strong><span>Funcionários · Belo Horizonte — MG</span></div>
        </li>
        <li class="ctm__linha">
          <span class="ctm__ico"><i class="ph ph-clock" aria-hidden="true"></i></span>
          <div><strong>Segunda a sexta, 8h às 18h</strong><span>Atendimento com hora marcada</span></div>
        </li>
        <li class="ctm__linha">
          <span class="ctm__ico"><i class="ph ph-phone" aria-hidden="true"></i></span>
          <div><strong>{{ \App\Services\Site::telefoneFormatado() }}</strong><span>Recepção, no horário comercial</span></div>
        </li>
      </ul>

      <div class="ctm__acoes rise" data-d="3">
        <a class="btn btn--primary" href="https://www.google.com/maps/search/?api=1&amp;query=R.+Cl%C3%A1udio+Manoel%2C+48+-+Funcion%C3%A1rios%2C+Belo+Horizonte+-+MG" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-map-trifold" aria-hidden="true"></i> Abrir no Google Maps</span></a>
        <a class="btn btn--outline" href="https://waze.com/ul?q=R.%20Cl%C3%A1udio%20Manoel%2C%2048%2C%20Belo%20Horizonte" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-navigation-arrow" aria-hidden="true"></i> Abrir no Waze</span></a>
      </div>
    </div>
  </div>
</section>

<!-- ===== cena 4 — chamada final: WhatsApp ===== -->
<!-- textos: Escopo das Páginas/Contato.md, seção 3 -->
<section class="scene ctw scheme-05" id="agendar" data-scene data-tone="light" aria-labelledby="agendar-titulo">
  <div class="scene__body">
    <span class="ctw__ico rise rise--zoom" aria-hidden="true"><i class="ph ph-whatsapp-logo"></i></span>
    <h2 class="t-h2 rise" data-d="1" id="agendar-titulo">Prefere agendar diretamente<br>com a nossa equipe de atendimento?</h2>
    <p class="ctw__lead rise" data-d="2">Chame no WhatsApp para <strong>verificar disponibilidade</strong> e marcar sua consulta de forma rápida.</p>
    <a class="btn btn--primary btn--grande rise rise--zoom" data-d="3" href="{{ \App\Services\Site::linkWhatsapp() }}" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-whatsapp-logo" aria-hidden="true"></i> Chamar no WhatsApp</span></a>
    <p class="ctw__nota rise" data-d="4">{{ \App\Services\Site::whatsappFormatado() }} · segunda a sexta, 8h às 18h</p>
  </div>
</section>
@endsection

@section('pos-rodape')
<!-- a barra fixa de agendamento não entra aqui: a página inteira já é o convite -->
@endsection
