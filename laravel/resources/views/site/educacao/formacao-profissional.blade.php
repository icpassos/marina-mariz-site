@extends('layouts.site', [
    'titulo' => 'Formação profissional — Dra. Marina Mariz',
    'descricao' => 'Mentoria, supervisão e formação técnica com a Dra. Marina Mariz para médicos, residentes, enfermeiras obstetras, doulas e equipes.',
    'canonical' => 'https://dramarinamariz.com.br/educacao/formacao-profissional',
    'ogImagem' => 'https://dramarinamariz.com.br/assets/img/formacao-discussao.webp',
])

@php
    $periodo = fn ($item) => $item->starts_at
        ? $item->starts_at->format('d/m').($item->ends_at ? ' a '.$item->ends_at->format('d/m') : '')
        : null;
    $dados = fn ($item) => [
        $item->modality?->getLabel(),
        $item->format?->getLabel(),
        $item->workload,
        $periodo($item),
        $item->seats ? $item->seats.' vagas' : null,
    ];

    // Formulario de interesse: erro so decora o formulario que falhou.
    $chave = 'interesse-formacao';
    $falhou = $errors->any() && old('form') === $chave;
    $erro = fn (string $campo): ?string => $falhou ? $errors->first($campo) : null;
    $primeiro = $falhou ? array_key_first($errors->messages()) : null;
    $modalidades = ['mentoria' => 'Mentoria', 'supervisao' => 'Supervisão', 'in-company' => 'In company', 'palestra' => 'Palestra', 'outra' => 'Outra'];
@endphp

@section('conteudo')
<!-- ===== cena 1 — herói ===== -->
<!-- textos: Escopo das Páginas/Formação Profissional.md, seção 1 -->
<section class="scene lvh scheme-04" id="formacao" data-scene data-tone="dark" aria-labelledby="formacao-titulo">
  <div class="lvh__halo" aria-hidden="true"></div>
  <div class="lvh__grain" aria-hidden="true"></div>
  <div class="scene__body">
    <div>
      <nav class="crumbs rise" aria-label="Trilha">
        <a href="/educacao">Educação</a>
        <i class="ph ph-caret-right" aria-hidden="true"></i>
        <span aria-current="page">Para profissionais</span>
      </nav>
      <h1 class="t-h1 rise" data-d="1" data-split id="formacao-titulo">Elevar o padrão<br>da assistência</h1>
      <p class="lvh__lead rise" data-d="2"><strong>Mentoria, supervisão e conteúdo técnico</strong> de quem vive<br>a prática diariamente. Para <strong>médicos</strong>, <strong>residentes</strong> e <strong>equipes</strong>.</p>
      <p class="lvh__lead rise" data-d="2"><strong>Casos reais</strong>, <strong>evidência</strong> lida com <strong>critério</strong> e <strong>discussão</strong><br>de conduta — <strong>sem palestra motivacional</strong> no meio.</p>
      <div class="lvh__actions rise" data-d="3">
        <a class="btn btn--primary" href="#lista"><span class="btn__label">Ver as formações <i class="ph ph-arrow-down" aria-hidden="true"></i></span></a>
        <a class="btn btn--outline" href="/educacao"><span class="btn__label">Tudo em Educação <i class="ph ph-arrow-right" aria-hidden="true"></i></span></a>
      </div>
    </div>

    <!-- as capas dos três primeiros itens publicados, em perspectiva.
         Sem catálogo publicado a pilha vinha vazia e o herói ficava com um
         buraco: o estado vazio cai nas fotos decorativas da página. -->
    <div class="stack rise rise--right" data-d="2" aria-hidden="true">
@forelse ($itens->take(3)->filter->imagem as $capa)
      <figure><img src="{{ $capa->imagem->url() }}" alt="" width="{{ $capa->imagem->width }}" height="{{ $capa->imagem->height }}" loading="lazy" decoding="async"></figure>
@empty
      <figure><img src="/assets/img/formacao-discussao.webp" alt="" width="1080" height="608" loading="lazy" decoding="async"></figure>
      <figure><img src="/assets/img/formacao-equipe.webp" alt="" width="1080" height="728" loading="lazy" decoding="async"></figure>
      <figure><img src="/assets/img/formacao-consulta.webp" alt="" width="1080" height="608" loading="lazy" decoding="async"></figure>
@endforelse
    </div>
  </div>
</section>

<!-- ===== cena 2 — temas abordados ===== -->
<!-- textos: Escopo das Páginas/Formação Profissional.md, seção 4 -->
<section class="scene lvp lvp--temas scheme-05" id="temas" data-scene data-tone="light" aria-labelledby="temas-titulo">
  <div class="scene__body">
    <h2 class="t-h2 rise" id="temas-titulo">Temas abordados</h2>
    <p class="lvp__lead rise" data-d="1">Os eixos técnicos que atravessam mentorias, supervisões e formações fechadas.</p>
    <ul class="temas rise" data-d="2">
      <li class="tema">Gestação de alto risco</li>
      <li class="tema">Medicina fetal</li>
      <li class="tema">Gemelaridade</li>
      <li class="tema">Pré-eclâmpsia</li>
      <li class="tema">Condução de casos complexos</li>
      <li class="tema">Comunicação de diagnóstico difícil</li>
      <li class="tema">Prática humanizada baseada em evidência</li>
    </ul>
  </div>
</section>

<!-- ===== cena 3 — lista ===== -->
<!-- textos: Escopo das Páginas/Formação Profissional.md, seção 2 · itens: Painel/01 — Catálogo Educação
     o botão usa o link externo: não existe página interna de formação -->
<section class="scene lvl lvl--colado scheme-05" id="lista" data-scene data-tone="light" aria-label="As formações">
    <div class="scene__body">
@forelse ($itens as $item)
@if ($loop->first)
      <ol class="lvl__grid">
@endif
        <li class="rise" data-d="1"><article class="book card">
          <figure class="book__shot">
@if ($item->imagem)
            <img src="{{ $item->imagem->url() }}" alt="{{ $item->imagem->is_decorative ? '' : $item->imagem->alt }}" width="{{ $item->imagem->width }}" height="{{ $item->imagem->height }}" loading="lazy" decoding="async">
@endif
          </figure>
          <h3 class="book__title">{{ $item->title }}</h3>
          @include('site.educacao.partes.meta', ['meta' => $dados($item)])
          <p class="book__desc">{{ $item->description }}</p>
          <div class="book__acoes">
@if (filled($item->external_url))
            <a class="btn btn--primary book__buy" href="{{ $item->external_url }}" target="_blank" rel="noopener"><span class="btn__label">Quero esse <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></a>
@endif
            <button class="btn btn--outline book__mais" type="button" data-abre="formacao-{{ $item->getKey() }}"><span class="btn__label">Saber mais</span></button>
          </div>
        </article></li>
@if ($loop->last)
      </ol>
@endif
@empty
      @include('site.educacao.partes.vazio', [
          'icone' => 'ph-medal',
          'titulo' => 'Em breve',
          'texto' => 'Ainda não há formação publicada por aqui. Assine para saber quando abrir a próxima turma.',
          'rotulo' => 'Quero receber novidades',
      ])
@endforelse

@foreach ($itens as $item)
    <dialog class="modal livro" id="formacao-{{ $item->getKey() }}" aria-labelledby="formacao-{{ $item->getKey() }}-titulo">
      <button class="modal__x" type="button" data-fecha aria-label="Fechar"><i class="ph ph-x" aria-hidden="true"></i></button>
      <div class="livro__corpo">
        <div class="livro__lado">
@if ($item->imagem)
          <img src="{{ $item->imagem->url() }}" alt="" width="{{ $item->imagem->width }}" height="{{ $item->imagem->height }}" loading="lazy" decoding="async">
@endif
@if (filled($item->external_url))
          <a class="btn btn--primary" href="{{ $item->external_url }}" target="_blank" rel="noopener"><span class="btn__label"><i class="ph ph-arrow-up-right" aria-hidden="true"></i> Quero esse</span></a>
@endif
        </div>
        <div class="livro__texto">
          <h3 class="t-h3" id="formacao-{{ $item->getKey() }}-titulo">{{ $item->title }}</h3>
          @include('site.educacao.partes.meta', ['meta' => $dados($item)])
          @include('site.educacao.partes.texto', ['item' => $item])
        </div>
      </div>
    </dialog>
@endforeach

    @include('site.educacao.partes.mais', ['itens' => $itens])
  </div>
</section>

<!-- ===== cena 4 — formulário de interesse ===== -->
<!-- textos: Escopo das Páginas/Formação Profissional.md, seção 5
     não é o formulário de paciente: cai na caixa de mensagens com origem "Formação Profissional" -->
<section class="scene lvn scheme-04" id="interesse" data-scene data-tone="dark" aria-labelledby="interesse-titulo">
  <div class="scene__body">
    <div>
      <h2 class="t-h2 rise" id="interesse-titulo">Quer levar uma formação<br>à sua equipe?</h2>
      <p class="lvn__lead rise" data-d="1">Conte o contexto do serviço e a modalidade que faz sentido.<br>A resposta vem com proposta de formato e carga horária.</p>
    </div>

@if (session('form') === $chave)
    <p class="bln__msg bln__msg--ok rise rise--right" data-d="2" role="status" tabindex="-1" autofocus><i class="ph ph-check-circle" aria-hidden="true"></i>Mensagem enviada. Respondemos em até 1 dia útil.</p>
@else
    <form class="bln__form rise rise--right" data-d="2" method="post" action="{{ route('formularios.formacao') }}" novalidate>
      @csrf
      <input type="hidden" name="submission_id" value="{{ old('submission_id', \Illuminate\Support\Str::uuid()) }}">
      <input type="hidden" name="iniciado_em" value="{{ old('iniciado_em', \Illuminate\Support\Facades\Crypt::encryptString((string) now()->timestamp)) }}">
      <input type="hidden" name="form" value="{{ $chave }}">
      <input type="hidden" name="origem" value="formacao-profissional">
      <input type="text" name="website" class="bln__trap" tabindex="-1" autocomplete="off" aria-hidden="true">
      <div class="bln__row">
        <p @class(['field', 'field--erro' => $erro('nome')])><label for="fp-nome">Nome</label><input id="fp-nome" name="nome" type="text" placeholder="Como podemos te chamar" autocomplete="name" required value="{{ old('nome') }}" @if ($erro('nome')) aria-invalid="true" aria-describedby="fp-nome-erro" @if ($primeiro === 'nome') autofocus @endif @endif>@if ($erro('nome'))<span class="field__erro" id="fp-nome-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('nome') }}</span>@endif</p>
        <p @class(['field', 'field--erro' => $erro('email')])><label for="fp-email">E-mail</label><input id="fp-email" name="email" type="email" placeholder="voce@instituicao.com.br" autocomplete="email" required value="{{ old('email') }}" @if ($erro('email')) aria-invalid="true" aria-describedby="fp-email-erro" @if ($primeiro === 'email') autofocus @endif @endif>@if ($erro('email'))<span class="field__erro" id="fp-email-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('email') }}</span>@endif</p>
      </div>
      <div class="bln__row">
        <p @class(['field', 'field--erro' => $erro('whatsapp')])><label for="fp-tel">Telefone</label><input id="fp-tel" name="whatsapp" type="tel" placeholder="(31) 90000-0000" autocomplete="tel" required value="{{ old('whatsapp') }}" @if ($erro('whatsapp')) aria-invalid="true" aria-describedby="fp-tel-erro" @if ($primeiro === 'whatsapp') autofocus @endif @endif>@if ($erro('whatsapp'))<span class="field__erro" id="fp-tel-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('whatsapp') }}</span>@endif</p>
        <p @class(['field', 'field--erro' => $erro('profissao')])><label for="fp-profissao">Profissão</label><input id="fp-profissao" name="profissao" type="text" placeholder="Médica, enfermeira obstetra, doula…" required value="{{ old('profissao') }}" @if ($erro('profissao')) aria-invalid="true" aria-describedby="fp-profissao-erro" @if ($primeiro === 'profissao') autofocus @endif @endif>@if ($erro('profissao'))<span class="field__erro" id="fp-profissao-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('profissao') }}</span>@endif</p>
      </div>
      <div class="bln__row">
        <p @class(['field', 'field--erro' => $erro('registro_profissional')])><label for="fp-registro">CRM / COREN <span class="field__opc">(opcional)</span></label><input id="fp-registro" name="registro_profissional" type="text" placeholder="CRM-MG 00.000" value="{{ old('registro_profissional') }}" @if ($erro('registro_profissional')) aria-invalid="true" aria-describedby="fp-registro-erro" @endif>@if ($erro('registro_profissional'))<span class="field__erro" id="fp-registro-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('registro_profissional') }}</span>@endif</p>
        <p @class(['field', 'field--erro' => $erro('instituicao')])><label for="fp-instituicao">Instituição</label><input id="fp-instituicao" name="instituicao" type="text" placeholder="Hospital, clínica ou serviço" required value="{{ old('instituicao') }}" @if ($erro('instituicao')) aria-invalid="true" aria-describedby="fp-instituicao-erro" @endif>@if ($erro('instituicao'))<span class="field__erro" id="fp-instituicao-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('instituicao') }}</span>@endif</p>
      </div>
      <div class="field"><span class="field__rotulo" id="fp-mod-label">Modalidade de interesse</span>
        <!-- dropdown do DS: o <input type="hidden"> é o que vai no POST -->
        <div class="dropdown-container" data-dropdown data-dropdown-kind="select">
          <button type="button" class="dropdown-button main-button" aria-haspopup="listbox" aria-expanded="false" aria-controls="fp-mod-lista" aria-labelledby="fp-mod-label fp-mod-valor">
            <span class="dropdown-title text-truncate" id="fp-mod-valor">{{ $modalidades[old('modalidade')] ?? 'Selecione' }}</span>
            <span class="dropdown-arrow"><i class="ph-bold ph-caret-down" aria-hidden="true"></i></span>
          </button>
          <div class="dropdown-list-container" aria-hidden="true" inert>
            <div class="dropdown-list-wrapper">
              <ul class="dropdown-list" id="fp-mod-lista" role="listbox" aria-labelledby="fp-mod-label">
            <li class="dropdown-list-item list-button" id="fp-mod-1" role="option" aria-selected="{{ old('modalidade') === 'mentoria' ? 'true' : 'false' }}" data-value="mentoria" data-icon="ph-users-three"><span class="dropdown-option__text">Mentoria</span><i class="ph-bold ph-check dropdown-option__check" aria-hidden="true"></i></li>
            <li class="dropdown-list-item list-button" id="fp-mod-2" role="option" aria-selected="{{ old('modalidade') === 'supervisao' ? 'true' : 'false' }}" data-value="supervisao" data-icon="ph-clipboard-text"><span class="dropdown-option__text">Supervisão</span><i class="ph-bold ph-check dropdown-option__check" aria-hidden="true"></i></li>
            <li class="dropdown-list-item list-button" id="fp-mod-3" role="option" aria-selected="{{ old('modalidade') === 'in-company' ? 'true' : 'false' }}" data-value="in-company" data-icon="ph-buildings"><span class="dropdown-option__text">In company</span><i class="ph-bold ph-check dropdown-option__check" aria-hidden="true"></i></li>
            <li class="dropdown-list-item list-button" id="fp-mod-4" role="option" aria-selected="{{ old('modalidade') === 'palestra' ? 'true' : 'false' }}" data-value="palestra" data-icon="ph-microphone-stage"><span class="dropdown-option__text">Palestra</span><i class="ph-bold ph-check dropdown-option__check" aria-hidden="true"></i></li>
            <li class="dropdown-list-item list-button" id="fp-mod-5" role="option" aria-selected="{{ old('modalidade') === 'outra' ? 'true' : 'false' }}" data-value="outra" data-icon="ph-dots-three-circle"><span class="dropdown-option__text">Outra</span><i class="ph-bold ph-check dropdown-option__check" aria-hidden="true"></i></li>
              </ul>
              <div class="floating-icon" aria-hidden="true"><i class="ph ph-users-three"></i></div>
            </div>
          </div>
          <span class="dropdown-status" role="status" aria-live="polite"></span>
          <input type="hidden" name="modalidade" value="{{ old('modalidade') }}" required>
        </div>
      </div>
      <p @class(['field', 'field--erro' => $erro('texto')])><label for="fp-mensagem">Mensagem</label><textarea id="fp-mensagem" name="texto" rows="4" placeholder="Conte o contexto do serviço e o que a equipe precisa" required @if ($erro('texto')) aria-invalid="true" aria-describedby="fp-mensagem-erro" @if ($primeiro === 'texto') autofocus @endif @endif>{{ old('texto') }}</textarea>@if ($erro('texto'))<span class="field__erro" id="fp-mensagem-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('texto') }}</span>@endif</p>
      <label class="bln__consent">
        <input type="checkbox" name="ciencia_politica" value="1" required @if ($erro('ciencia_politica')) aria-invalid="true" aria-describedby="fp-ciencia-erro" autofocus @endif>
        <span>Li e estou ciente da <a href="/politica-de-privacidade">Política de Privacidade</a></span>
@if ($erro('ciencia_politica'))
        <span class="field__erro" id="fp-ciencia-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('ciencia_politica') }}</span>
@endif
      </label>
      <button class="btn btn--primary" type="submit"><span class="btn__label">Enviar interesse <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></button>
    </form>
@endif
  </div>
</section>

<!-- ===== cena final — newsletter ===== -->
<!-- textos: Escopo das Páginas/Formação Profissional.md, seção 6 · destino: Painel/04 — Newsletter
     lista única de newsletter; o campo origem diz de qual página veio a inscrição -->
<section class="scene lvn scheme-05" id="avisos" data-scene data-tone="light" aria-labelledby="avisos-titulo">
  <div class="scene__body">
    <div>
      <h2 class="t-h2 rise" id="avisos-titulo">Quer acompanhar as<br>próximas formações?</h2>
      <p class="lvn__lead rise" data-d="1">Turmas, aulas abertas e conteúdo técnico novo, avisados<br><strong>por e-mail antes de virarem cartaz</strong>.</p>
    </div>

    @include('site.educacao.partes.newsletter', [
        'chave' => 'newsletter-formacao',
        'origem' => 'formacao-profissional',
        'prefixo' => 'nf',
        'rotulo' => 'Quero receber novidades',
    ])
  </div>
</section>
@endsection

@section('pos-rodape')
@include('site.partials.barra')
@endsection
