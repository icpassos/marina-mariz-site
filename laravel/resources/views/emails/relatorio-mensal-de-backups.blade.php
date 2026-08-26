{{-- Sem dado pessoal: so datas, contagens e caminho. --}}
<p>Backups dos contatos em {{ $mes->translatedFormat('F \d\e Y') }}.</p>

@if ($execucoes->isEmpty())
    <p><strong>Nenhum backup foi executado no mês.</strong> Vale conferir o cron no hPanel.</p>
@else
    <table>
        <tr><th>Data</th><th>Mensagens</th><th>Leads</th><th>Newsletter</th></tr>
        @foreach ($execucoes as $execucao)
            <tr>
                <td>{{ $execucao->executado_em->format('d/m/Y H:i') }}</td>
                <td>{{ $execucao->mensagens }}</td>
                <td>{{ $execucao->leads }}</td>
                <td>{{ $execucao->newsletter }}</td>
            </tr>
        @endforeach
    </table>

    <p>Total do mês: {{ $total }} registro(s) em {{ $execucoes->count() }} execução(ões).</p>
    <p>Pasta: <code>{{ $execucoes->last()->caminho }}</code></p>

    @if ($total === 0)
        <p>Nenhum contato novo no mês. Sem movimento, nenhum arquivo é criado — isso é esperado.</p>
    @endif
@endif

@if ($semanasSemBackup !== [])
    <p><strong>Semanas sem execução:</strong></p>
    <ul>
        @foreach ($semanasSemBackup as $semana)
            <li>{{ $semana }}</li>
        @endforeach
    </ul>
@endif
