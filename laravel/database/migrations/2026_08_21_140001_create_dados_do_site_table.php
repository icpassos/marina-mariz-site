<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fonte unica de contato, identificacao profissional, endereco e redes
 * (doc 03). Linha unica: o site inteiro le daqui.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dados_do_site', function (Blueprint $table) {
            $table->id();

            // Contato
            $table->string('email')->nullable();
            // So digitos. A exibicao e os links tel:/wa.me sao montados na
            // leitura, para nao existirem dois formatos no banco.
            $table->string('telefone', 11)->nullable();
            $table->string('whatsapp', 11)->nullable();
            $table->string('horario_atendimento')->nullable();

            // Identificacao profissional exigida pela Resolucao CFM 2.336/2023
            $table->string('nome_divulgacao')->nullable();
            $table->string('palavra_obrigatoria')->nullable();
            $table->string('crm')->nullable();
            $table->json('especialidades')->nullable();

            // Endereco
            $table->string('endereco_logradouro')->nullable();
            $table->string('endereco_complemento')->nullable();
            $table->string('endereco_bairro')->nullable();
            $table->string('endereco_cidade')->nullable();
            $table->string('endereco_estado', 2)->nullable();
            $table->string('endereco_cep', 8)->nullable();

            // Redes: chave => {url, visivel}. Conjunto fixo do doc 03, por
            // isso JSON e nao tabela.
            $table->json('redes')->nullable();

            $table->timestamps();
        });

        // Valores do doc 03 / Rodape. O singleton ja nasce valido para o
        // site nao ficar sem telefone entre o deploy e o primeiro acesso.
        DB::table('dados_do_site')->insert([
            'email' => 'contato@dramarinamariz.com.br',
            'telefone' => '3130902320',
            'whatsapp' => '31996082883',
            'horario_atendimento' => 'Segunda a Sexta, 8h às 18h',
            'nome_divulgacao' => 'Marina Mariz',
            'palavra_obrigatoria' => 'MÉDICO',
            'crm' => 'CRM-MG 48.386',
            'especialidades' => json_encode([
                // Valores em uso no rodape de todas as paginas do site.
                ['especialidade' => 'Ginecologia e Obstetrícia', 'rqe' => '30.992'],
                ['especialidade' => 'Medicina Fetal', 'rqe' => '30.993'],
            ], JSON_UNESCAPED_UNICODE),
            'endereco_logradouro' => 'R. Cláudio Manoel, 48',
            'endereco_complemento' => 'Sala 1201',
            'endereco_bairro' => 'Funcionários',
            'endereco_cidade' => 'Belo Horizonte',
            'endereco_estado' => 'MG',
            'endereco_cep' => '30140100',
            'redes' => json_encode([
                'instagram' => ['url' => 'https://www.instagram.com/dramarinamariz', 'visivel' => true],
                'youtube' => ['url' => 'https://www.youtube.com/@SemNeuraPodcast', 'visivel' => true],
                'linkedin' => ['url' => 'https://www.linkedin.com/in/marinamariz', 'visivel' => true],
                'spotify' => ['url' => 'https://open.spotify.com/show/semneurapodcast', 'visivel' => true],
                'whatsapp' => ['visivel' => true],
                'comunidade' => ['url' => 'https://comunidade.dramarinamariz.com.br', 'visivel' => true],
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('dados_do_site');
    }
};
