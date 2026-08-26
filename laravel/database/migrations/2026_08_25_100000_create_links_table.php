<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();

            // O que aparece escrito no botao.
            $table->string('title');

            // Endereco de destino. Pode ser de fora (Instagram, YouTube) ou
            // do proprio site (`/blog`, `/educacao/ebooks`). Nulo porque um
            // rascunho precisa so do titulo — a exigencia e para publicar.
            $table->string('url')->nullable();

            // Linha de apoio embaixo do titulo. Opcional.
            $table->string('description')->nullable();

            // status, position e image_id (miniatura opcional).
            $table->conteudoBase();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
