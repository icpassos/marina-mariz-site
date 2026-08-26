<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Politica de Privacidade e Termos de Uso no banco, nao em arquivo
 * Markdown lido em producao (exigencia do doc 03).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('textos_legais', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->unique();

            // Copia de trabalho: e o que o administrador edita.
            $table->string('titulo')->nullable();
            $table->longText('conteudo')->nullable();
            $table->string('status')->default('rascunho');

            // Copia em uso no site. "Salvar rascunho nao muda pagina
            // publica" so e verdade com as duas copias separadas.
            $table->string('titulo_publicado')->nullable();
            $table->longText('conteudo_publicado')->nullable();
            $table->timestamp('publicado_em')->nullable();

            $table->timestamps();
        });

        foreach ([
            'politica-de-privacidade' => 'Política de Privacidade',
            'termos-de-uso' => 'Termos de Uso',
        ] as $chave => $titulo) {
            DB::table('textos_legais')->insert([
                'chave' => $chave,
                'titulo' => $titulo,
                'status' => 'rascunho',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('textos_legais');
    }
};
