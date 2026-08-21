<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();

            // Nome fisico no disco privado. UUID para o nome original nunca
            // virar caminho adivinhavel.
            $table->uuid('uuid')->unique();
            $table->string('path')->unique();
            $table->string('original_name');

            $table->string('name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');

            // Só imagem tem dimensao.
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            // Imagem informativa exige alt; decorativa salva alt vazio de
            // proposito, e a coluna registra que foi escolha e nao esquecimento.
            $table->string('alt')->nullable();
            $table->boolean('is_decorative')->default(false);

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('mime_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
