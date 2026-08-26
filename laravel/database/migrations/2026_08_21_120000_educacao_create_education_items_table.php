<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalogo Educacao (doc 01): uma tabela so, com um campo TIPO. As colunas
 * especificas ficam nulas nos tipos que nao as usam — seis tabelas seriam
 * seis CRUDs para o mesmo conteudo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_items', function (Blueprint $table) {
            $table->id();

            $table->string('type')->index();
            $table->string('title');
            $table->text('description')->nullable();

            // status, position e image_id
            $table->conteudoBase();
            $table->conteudoSeo();

            // Livro
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('publisher')->nullable();

            // Compra/inscricao: livro, e-book pago, curso, formacao e evento.
            $table->string('external_url')->nullable();

            // E-book
            $table->boolean('is_free')->default(false);
            // Arquivo em uso nao pode sumir da biblioteca sem aviso (doc 05).
            $table->foreignId('pdf_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->foreignId('epub_id')->nullable()->constrained('media')->restrictOnDelete();

            // Curso e formacao
            $table->string('format')->nullable();
            $table->string('modality')->nullable();
            $table->text('audience')->nullable();
            $table->string('workload', 60)->nullable();
            $table->unsignedInteger('seats')->nullable();

            // Curso, formacao (data) e evento (data e hora).
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('ends_at')->nullable();

            // Evento
            $table->string('event_format')->nullable();
            $table->string('venue_name')->nullable();
            $table->string('venue_address')->nullable();
            $table->string('venue_city')->nullable();
            $table->string('venue_state', 2)->nullable();

            // Material gratuito
            $table->string('material_format')->nullable();
            $table->foreignId('file_id')->nullable()->constrained('media')->restrictOnDelete();
            $table->boolean('requires_email')->default(true);
            $table->string('lead_source')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_items');
    }
};
