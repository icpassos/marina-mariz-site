Backups dos contatos em {{ $mes->translatedFormat('F \d\e Y') }}.

@if ($execucoes->isEmpty())
Nenhum backup foi executado no mês. Vale conferir o cron no hPanel.
@else
@foreach ($execucoes as $execucao)
{{ $execucao->executado_em->format('d/m/Y H:i') }} — mensagens: {{ $execucao->mensagens }}, leads: {{ $execucao->leads }}, newsletter: {{ $execucao->newsletter }}
@endforeach

Total do mês: {{ $total }} registro(s) em {{ $execucoes->count() }} execução(ões).
Pasta: {{ $execucoes->last()->caminho }}
@if ($total === 0)

Nenhum contato novo no mês. Sem movimento, nenhum arquivo é criado — isso é esperado.
@endif
@endif
@if ($semanasSemBackup !== [])

Semanas sem execução:
@foreach ($semanasSemBackup as $semana)
- {{ $semana }}
@endforeach
@endif
