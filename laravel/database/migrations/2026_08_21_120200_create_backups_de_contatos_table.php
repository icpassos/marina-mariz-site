<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marco de cada backup semanal de contatos bem-sucedido.
 *
 * E daqui que sai o recorte do "que e novo" — nunca "ultimos 7 dias". Se o
 * cron ficar tres semanas parado, a execucao seguinte leva as tres semanas
 * inteiras, sem buraco.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups_de_contatos', function (Blueprint $table) {
            $table->id();

            $table->timestamp('executado_em')->index();

            $table->unsignedInteger('mensagens')->default(0);
            $table->unsignedInteger('leads')->default(0);
            $table->unsignedInteger('newsletter')->default(0);

            // Pasta realmente usada: a configurada ou o fallback.
            $table->string('caminho');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups_de_contatos');
    }
};
