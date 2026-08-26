<?php

namespace App\Support;

/**
 * O que a outbox precisa saber de um registro para entregar o e-mail de
 * aviso sem conhecer o modelo por dentro (doc 08).
 */
interface VaiParaOutbox
{
    public function assuntoDoEmail(): string;

    /** Responder-para: o e-mail de quem escreveu. */
    public function responderPara(): string;

    /**
     * Linhas do e-mail de aviso. Sem texto da mensagem, telefone, registro
     * profissional ou anotacao interna (doc 04).
     *
     * @return array<string, string>
     */
    public function linhasDoEmail(): array;

    /** Link direto para o registro autenticado no painel. */
    public function linkNoPainel(): string;
}
