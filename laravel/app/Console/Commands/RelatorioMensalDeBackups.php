<?php

namespace App\Console\Commands;

use App\Mail\RelatorioMensalDeBackups as Email;
use App\Models\BackupDeContatos;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

/**
 * Acompanhamento mensal dos backups semanais de contatos.
 *
 * O e-mail sai todo mes mesmo sem movimento: a ausencia dele e que significa
 * problema. O alarme rapido fica no quadro do painel.
 */
class RelatorioMensalDeBackups extends Command
{
    protected $signature = 'backup:relatorio {--mes= : Mes de referencia AAAA-MM (padrao: o mes passado)}';

    protected $description = 'Envia por e-mail o resumo mensal dos backups de contatos';

    public function handle(): int
    {
        $mes = Carbon::parse($this->option('mes') ?: now()->subMonth()->format('Y-m').'-01')->startOfMonth();
        $fim = $mes->copy()->endOfMonth();

        $execucoes = BackupDeContatos::query()
            ->whereBetween('executado_em', [$mes, $fim])
            ->orderBy('executado_em')
            ->get();

        Mail::to(config('pessoas.email_equipe'))
            ->send(new Email($mes, $execucoes, $this->semanasSemBackup($mes, $fim, $execucoes)));

        $this->info($execucoes->count().' execução(ões) em '.$mes->format('m/Y').'.');

        return self::SUCCESS;
    }

    /**
     * Blocos de 7 dias a partir do dia 1 que nao tiveram execucao. E a
     * informacao mais util do e-mail: buraco no cron aparece aqui.
     *
     * @param  Collection<int, BackupDeContatos>  $execucoes
     * @return list<string>
     */
    private function semanasSemBackup(Carbon $mes, Carbon $fim, Collection $execucoes): array
    {
        $semanas = [];

        for ($inicio = $mes->copy(); $inicio <= $fim; $inicio = $inicio->copy()->addWeek()) {
            $fimDaSemana = min($inicio->copy()->addDays(6)->endOfDay(), $fim);

            $teve = $execucoes->contains(
                fn (BackupDeContatos $m): bool => $m->executado_em->between($inicio, $fimDaSemana)
            );

            if (! $teve) {
                $semanas[] = $inicio->format('d/m').' a '.$fimDaSemana->format('d/m');
            }
        }

        return $semanas;
    }
}
