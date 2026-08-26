{{--
    Janela de download do material gratuito e do e-book gratuito — a mesma
    nos dois lugares (escopo: Materiais Gratuitos, secao 3).

    Grava o lead com a origem do item e devolve link assinado de 3 dias.
    Ciencia da Politica e obrigatoria; receber novidades e opcional e nasce
    desmarcado. Nada sai por e-mail: o e-mail gera o lead, a entrega e agora.

    Parametros: item · id.
--}}
@php
    $chave = 'material-'.$item->getKey();
    $falhou = $errors->any() && old('form') === $chave;
    $erro = fn (string $campo): ?string => $falhou ? $errors->first($campo) : null;
    $primeiro = $falhou ? array_key_first($errors->messages()) : null;
@endphp
    <dialog class="modal baixar" id="{{ $id }}" aria-labelledby="{{ $id }}-titulo">
      <button class="modal__x" type="button" data-fecha aria-label="Fechar"><i class="ph ph-x" aria-hidden="true"></i></button>
      <div class="baixar__corpo">
        <h3 class="t-h3" id="{{ $id }}-titulo">Antes de baixar, se identifique</h3>
        <p class="baixar__nota">{{ $item->title }}</p>
        <form class="bln__form" method="post" action="{{ route('formularios.material') }}" novalidate>
          @csrf
          <input type="hidden" name="submission_id" value="{{ old('submission_id', \Illuminate\Support\Str::uuid()) }}">
          <input type="hidden" name="form" value="{{ $chave }}">
          <input type="hidden" name="media_id" value="{{ $item->comoMaterial()['arquivo']?->getKey() }}">
          <input type="text" name="website" class="bln__trap" tabindex="-1" autocomplete="off" aria-hidden="true">
          <div class="bln__row">
            <p @class(['field', 'field--erro' => $erro('nome')])><label for="{{ $id }}-nome">Nome</label><input id="{{ $id }}-nome" name="nome" type="text" placeholder="Como podemos te chamar" autocomplete="name" required value="{{ old('nome') }}" @if ($erro('nome')) aria-invalid="true" aria-describedby="{{ $id }}-nome-erro" @if ($primeiro === 'nome') autofocus @endif @endif>@if ($erro('nome'))<span class="field__erro" id="{{ $id }}-nome-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('nome') }}</span>@endif</p>
            <p @class(['field', 'field--erro' => $erro('email')])><label for="{{ $id }}-email">E-mail</label><input id="{{ $id }}-email" name="email" type="email" placeholder="seu@email.com" autocomplete="email" required value="{{ old('email') }}" @if ($erro('email')) aria-invalid="true" aria-describedby="{{ $id }}-email-erro" @if ($primeiro === 'email') autofocus @endif @endif>@if ($erro('email'))<span class="field__erro" id="{{ $id }}-email-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('email') }}</span>@endif</p>
          </div>
          <label class="bln__consent">
            <input type="checkbox" name="ciencia_politica" value="1" required @if ($erro('ciencia_politica')) aria-invalid="true" aria-describedby="{{ $id }}-ciencia-erro" autofocus @endif>
            <span>Li e estou ciente da <a href="/politica-de-privacidade">Política de Privacidade</a></span>
@if ($erro('ciencia_politica'))
            <span class="field__erro" id="{{ $id }}-ciencia-erro" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i>{{ $erro('ciencia_politica') }}</span>
@endif
          </label>
          <label class="bln__consent">
            <input type="checkbox" name="aceita_newsletter" value="1">
            <span>Quero receber novidades por e-mail</span>
          </label>
          <button class="btn btn--primary" type="submit"><span class="btn__label"><i class="ph ph-download-simple" aria-hidden="true"></i> Baixar agora</span></button>
        </form>
      </div>
    </dialog>
