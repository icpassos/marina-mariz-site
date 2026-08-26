<?php

namespace App\Console\Commands;

use App\Http\Controllers\DownloadProtegidoController;
use App\Mail\AvisoDeFormulario;
use App\Models\Lead;
use App\Models\OutboxJob;
use App\Support\Outbox;
use App\Support\VaiParaOutbox;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Entrega os avisos pendentes da outbox fora da requisicao HTTP (doc 08).
 *
 * Chamado pelo Scheduler a cada 5 minutos pelo cron do hPanel. Nao e daemon:
 * se o cron parar, o formulario continua gravando e as pendencias saem na
 * proxima execucao.
 */
class ProcessarOutbox extends Command
{
    protected $signature = 'outbox:processar';

    protected $description = 'Entrega o e-mail de aviso dos envios pendentes';

    public function handle(): int
    {
        $backoff = config('pessoas.outbox.backoff');

        $pendentes = OutboxJob::query()
            ->where('status', OutboxJob::PENDENTE)
            ->where('proxima_tentativa_em', '<=', now())
            ->with('assunto')
            ->orderBy('id')
            ->limit(config('pessoas.outbox.lote'))
            ->get();

        foreach ($pendentes as $item) {
            // Registro principal apagado do painel: nao ha o que entregar.
            if (! $item->assunto instanceof VaiParaOutbox) {
                $item->update(['status' => OutboxJob::FALHOU, 'ultimo_erro' => 'Registro removido']);

                continue;
            }

            try {
                Mail::to(config('pessoas.email_equipe'))
                    ->send(new AvisoDeFormulario($item->assunto, $item->submission_id, $this->linkDoMaterial($item)));

                $item->update([
                    'status' => OutboxJob::ENTREGUE,
                    'tentativas' => $item->tentativas + 1,
                    'ultimo_erro' => null,
                    'entregue_em' => now(),
                    'proxima_tentativa_em' => null,
                ]);
            } catch (Throwable $e) {
                // Sem report(): o rastreamento poderia arrastar o registro
                // para o log, e o doc 08 proibe.
                $tentativas = $item->tentativas + 1;
                $espera = $backoff[$tentativas - 1] ?? null;

                $item->update([
                    'status' => $espera === null ? OutboxJob::FALHOU : OutboxJob::PENDENTE,
                    'tentativas' => $tentativas,
                    'ultimo_erro' => Outbox::erroCurto($e),
                    'proxima_tentativa_em' => $espera === null ? null : now()->addMinutes($espera),
                ]);
            }
        }

        $this->info($pendentes->count().' item(ns) processado(s).');

        return self::SUCCESS;
    }

    /**
     * Doc 08: o mesmo link de 3 dias que apareceu na tela vai no e-mail da
     * outbox. E o link do painel para a equipe, nao entrega para o visitante.
     */
    private function linkDoMaterial(OutboxJob $item): ?string
    {
        $registro = $item->assunto;

        if (! $registro instanceof Lead) {
            return null;
        }

        $material = $registro->downloadDe($item->submission_id)?->material;

        return $material ? DownloadProtegidoController::linkPara($material) : null;
    }
}
