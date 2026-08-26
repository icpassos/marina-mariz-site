<?php

namespace App\Mail;

use App\Models\BackupDeContatos;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Acompanhamento mensal dos backups de contatos.
 *
 * Vai todo mes, mesmo em mes sem movimento: e a ausencia do e-mail, nao a
 * ausencia de arquivo, que denuncia cron parado. O alarme rapido e o quadro
 * do painel; aqui o tom e de acompanhamento.
 */
class RelatorioMensalDeBackups extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  Collection<int, BackupDeContatos>  $execucoes
     * @param  list<string>  $semanasSemBackup  intervalos "dd/mm a dd/mm"
     */
    public function __construct(
        public Carbon $mes,
        public Collection $execucoes,
        public array $semanasSemBackup,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Site] Backups dos contatos — '.$this->mes->translatedFormat('F/Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.relatorio-mensal-de-backups',
            text: 'emails.relatorio-mensal-de-backups-texto',
            with: ['total' => $this->execucoes->sum(fn (BackupDeContatos $m): int => $m->total())],
        );
    }
}
