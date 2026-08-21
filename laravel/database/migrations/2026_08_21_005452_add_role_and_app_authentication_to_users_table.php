<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ENUM e nao string: o proprio banco recusa papel invalido,
            // em vez de depender de validacao em PHP para cada gravacao.
            $table->enum('role', ['administrator', 'editor'])
                ->default('editor')
                ->after('email');

            // TOTP do Filament. Guardados criptografados pela APP_KEY,
            // por isso texto e nao coluna de tamanho fixo.
            $table->text('app_authentication_secret')->nullable();
            $table->text('app_authentication_recovery_codes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'app_authentication_secret',
                'app_authentication_recovery_codes',
            ]);
        });
    }
};
