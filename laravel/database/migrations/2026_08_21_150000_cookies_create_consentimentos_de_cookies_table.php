<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prova de cada escolha feita no Aviso de Cookies (doc 00, LGPD).
 * A recusa tambem e gravada: e ela que prova que a escolha foi respeitada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consentimentos_de_cookies', function (Blueprint $table) {
            $table->id();

            $table->string('versao_aviso');
            $table->boolean('analiticos');
            $table->boolean('marketing');

            // Nunca IP em claro: so o HMAC do doc 08. Sem IP_HASH_KEY,
            // nao se grava IP nenhum.
            $table->string('ip_hash', 64)->nullable();

            $table->timestamp('decidido_em');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consentimentos_de_cookies');
    }
};
