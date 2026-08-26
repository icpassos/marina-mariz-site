{{ $registro->assuntoDoEmail() }}

@foreach ($registro->linhasDoEmail() as $rotulo => $valor)
{{ $rotulo }}: {{ $valor }}
@endforeach
Envio: {{ $submissionId }}

Abrir no painel: {{ $registro->linkNoPainel() }}
@if ($linkDeDownload)

Link do material (vale 3 dias): {{ $linkDeDownload }}
@endif
