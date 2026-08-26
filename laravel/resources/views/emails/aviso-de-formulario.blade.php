{{-- Todo valor sai escapado pelo Blade. Nunca usar {!! !!} aqui. --}}
<p>{{ $registro->assuntoDoEmail() }}</p>

<ul>
    @foreach ($registro->linhasDoEmail() as $rotulo => $valor)
        <li><strong>{{ $rotulo }}:</strong> {{ $valor }}</li>
    @endforeach
    <li><strong>Envio:</strong> {{ $submissionId }}</li>
</ul>

<p><a href="{{ $registro->linkNoPainel() }}">Abrir no painel</a></p>

@if ($linkDeDownload)
    <p><a href="{{ $linkDeDownload }}">Link do material (vale 3 dias)</a></p>
@endif
