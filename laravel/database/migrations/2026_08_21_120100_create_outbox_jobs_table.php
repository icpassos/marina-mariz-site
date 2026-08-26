<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Outbox transacional do e-mail de aviso (doc 08).
 *
 * A linha nasce na mesma transacao do registro principal. O corpo do
 * formulario NAO e copiado para ca: o comando le o registro pelo morph na
 * hora de entregar, entao nao existe segunda copia do dado pessoal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbox_jobs', function (Blueprint $table) {
            $table->id();

            // Chave de idempotencia do envio. Unica: a mesma chave chegando
            // duas vezes nao vira dois avisos.
            $table->uuid('submission_id')->unique();

            $table->morphs('assunto');

            $table->string('status', 20)->default('pendente');
            $table->unsignedTinyInteger('tentativas')->default(0);

            // Sem mensagem crua de excecao: so classe e codigo, para nunca
            // arrastar corpo de formulario nem credencial para o painel.
            $table->string('ultimo_erro')->nullable();

            $table->timestamp('proxima_tentativa_em')->nullable();
            $table->timestamp('entregue_em')->nullable();

            $table->timestamps();

            $table->index(['status', 'proxima_tentativa_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbox_jobs');
    }
};
