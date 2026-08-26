{{--
    Bloco de inscricao que fecha toda pagina de Educacao. Layout, campos e
    consentimento sao identicos em todas; so mudam titulo, descricao e
    rotulo do botao (escopos das paginas).

    E uma lista so: `origem` guarda apenas de qual pagina veio a inscricao.

    Parametros: chave · origem · prefixo (dos ids) · rotulo (do botao).
--}}
@php
    // Erro so decora o formulario que falhou: a pagina tem mais de um.
    $falhou = $errors->any() && old('form') === $chave;
    $erro = fn (string $campo): ?string => $falhou ? $errors->first($campo) : null;
    $primeiro = $falhou ? array_key_first($errors->messages()) : null;
@endphp
@if (session('form') === $chave)
    <p class="bln__msg bln__msg--{{ session('novo', true) ? 'ok' : 'dup' }} rise rise--right" data-d="2" role="status" tabindex="-1" autofocus><i class="ph ph-{{ session('novo', true) ? 'check-circle' : 'info' }}" aria-hidden="true"></i>{{ session('novo', true) ? 'Pronto! Você vai receber as novidades no seu e-mail.' : 'Você já está na lista. Fique de olho no e-mail.' }}</p>
@else
    <form class="bln__form rise rise--right" data-d="2" method="post" action="{{ route('formularios.newsletter') }}" novalidate>
      @csrf
      <input type="hidden" name="submission_id" value="{{ old('submission_id', \Illuminate\Support\Str::uuid()) }}">
      <input type="hidden" name="form" value="{{ $chave }}">
      {{-- A caixa unica da tela responde pelas duas exigencias do texto que
           ela traz: aceitar a newsletter e ter lido a Politica. --}}
      <input type="hidden" name="ciencia_politica" value="1">
      <input type="hidden" name="origem" value="{{ $origem }}">
      <input type="text" name="website" class="bln__trap" tabindex="-1" autocomplete="off" aria-hidden="true">
      <div class="bln__row">
        <p @class(['field', 'field--erro' => $erro('nome')])><label for="{{ $prefixo }}-nome">Nome</label><input id="{{ $prefixo }}-nome" name="nome" type="text" placeholder="Como podemos te chamar" autocomplete="name" required value="{{ old('nome') }}" @if ($erro('nome')) aria-invalid="true" aria-describedby="{{ $prefixo }}-nome-erro" @if ($primeiro === 'nome') autofocus @endif @endif>@if ($erro('nome'))<span class="field__erro" id="{{ $prefixo }}-nome-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('nome') }}</span>@endif</p>
        <p @class(['field', 'field--erro' => $erro('email')])><label for="{{ $prefixo }}-email">E-mail</label><input id="{{ $prefixo }}-email" name="email" type="email" placeholder="seu@email.com" autocomplete="email" required value="{{ old('email') }}" @if ($erro('email')) aria-invalid="true" aria-describedby="{{ $prefixo }}-email-erro" @if ($primeiro === 'email') autofocus @endif @endif>@if ($erro('email'))<span class="field__erro" id="{{ $prefixo }}-email-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('email') }}</span>@endif</p>
      </div>
      <label class="bln__consent">
        <input type="checkbox" name="aceita_newsletter" value="1" required @if ($erro('aceita_newsletter')) aria-invalid="true" aria-describedby="{{ $prefixo }}-consent-erro" autofocus @endif>
        <span>Quero receber novidades por e-mail e li a <a href="/politica-de-privacidade">Política de Privacidade</a></span>
@if ($erro('aceita_newsletter'))
        <span class="field__erro" id="{{ $prefixo }}-consent-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('aceita_newsletter') }}</span>
@endif
      </label>
      <button class="btn btn--primary" type="submit"><span class="btn__label">{{ $rotulo }} <i class="ph ph-arrow-up-right" aria-hidden="true"></i></span></button>
    </form>
@endif
