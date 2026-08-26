<?php

namespace App\Support;

use App\Models\OutboxJob;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Outbox transacional (doc 08).
 *
 * Chamado DENTRO da transacao que grava o registro principal: ou os dois
 * gravam, ou nada grava. Se o cron parar, o formulario continua gravando e
 * os itens saem quando ele voltar.
 */
class Outbox
{
    /**
     * Um item por envio. O `submission_id` unico e quem garante que repetir
     * a chave de idempotencia nao duplica o aviso.
     */
    public static function enfileirar(Model&VaiParaOutbox $registro, string $submissionId): void
    {
        OutboxJob::firstOrCreate(
            ['submission_id' => $submissionId],
            [
                'assunto_type' => $registro->getMorphClass(),
                'assunto_id' => $registro->getKey(),
                'status' => OutboxJob::PENDENTE,
                'proxima_tentativa_em' => now(),
            ],
        );
    }

    /**
     * Resumo de erro seguro para o painel: classe e codigo, nunca a mensagem
     * crua. Mensagem de SMTP pode devolver o que foi enviado, e o doc 08
     * proibe registrar corpo de formulario em qualquer lugar.
     */
    public static function erroCurto(Throwable $e): string
    {
        return class_basename($e).' #'.$e->getCode();
    }
}
