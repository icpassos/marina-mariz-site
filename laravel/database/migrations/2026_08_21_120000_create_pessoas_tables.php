<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mensagens, leads, newsletter e consentimentos (doc 04).
 *
 * Nenhuma tabela aqui tem coluna de IP em claro: so o HMAC do doc 08.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensagens', function (Blueprint $table) {
            $table->id();

            // Chave de idempotencia do envio. Unica: reenviar a mesma chave
            // nao cria segunda mensagem.
            $table->uuid('submission_id')->unique();

            $table->string('origem')->index();
            $table->string('nome');
            $table->string('email');
            $table->string('whatsapp')->nullable();
            $table->text('texto');

            // Extras que so o formulario de Formacao Profissional envia.
            $table->string('profissao')->nullable();
            $table->string('registro_profissional')->nullable();
            $table->string('instituicao')->nullable();
            $table->string('modalidade')->nullable();

            $table->string('status')->default('nova')->index();
            $table->text('anotacao')->nullable();

            // Ciencia da Politica: versao do aviso + IP pseudonimizado.
            $table->string('politica_versao');
            $table->string('ip_hash', 64)->nullable();

            $table->timestamps();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            // Deduplicacao por e-mail: uma pessoa, um lead, historico crescente.
            $table->string('email')->unique();
            $table->string('nome');
            $table->string('telefone')->nullable();
            $table->boolean('eh_cliente')->default(false)->index();

            $table->string('politica_versao');
            $table->string('ip_hash', 64)->nullable();

            $table->timestamps();
        });

        Schema::create('lead_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();

            // Um download por envio. Repetir a chave nao soma linha no historico.
            $table->uuid('submission_id')->unique();

            $table->string('origem');
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();

            $table->timestamps();
        });

        Schema::create('inscricoes_newsletter', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('nome')->nullable();
            $table->string('origem')->index();
            $table->string('status')->default('ativo')->index();

            $table->uuid('submission_id');

            // Token de uso unico do link de descadastro. Zerado ao ser usado.
            $table->string('descadastro_token', 64)->nullable()->unique();
            $table->timestamp('descadastrado_em')->nullable();

            $table->string('politica_versao');
            $table->string('ip_hash', 64)->nullable();

            $table->timestamps();
        });

        Schema::create('consentimentos', function (Blueprint $table) {
            $table->id();
            $table->morphs('consentivel');

            // Finalidade especifica; consentimento generico nao vale (doc 04).
            $table->string('finalidade');
            $table->string('versao_aviso');
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('concedido_em');
            $table->timestamp('revogado_em')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consentimentos');
        Schema::dropIfExists('inscricoes_newsletter');
        Schema::dropIfExists('lead_downloads');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('mensagens');
    }
};
