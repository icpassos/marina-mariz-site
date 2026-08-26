<?php

namespace App\Mail;

use App\Support\VaiParaOutbox;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso para a equipe a cada registro novo (doc 04).
 *
 * Ninguem abre painel todo dia; sem este e-mail a caixa vira cemiterio.
 * O corpo traz so nome, origem, submission_id e o link do painel — nunca o
 * texto da mensagem, telefone, registro profissional ou anotacao.
 */
class AvisoDeFormulario extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public VaiParaOutbox $registro,
        public string $submissionId,
        public ?string $linkDeDownload = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->registro->assuntoDoEmail(),
            // Responder-para o visitante: da para responder do celular sem
            // abrir o painel.
            replyTo: [new Address($this->registro->responderPara())],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.aviso-de-formulario',
            text: 'emails.aviso-de-formulario-texto',
        );
    }
}
