<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SEO padrao e IDs de Analytics (doc 06). Linha unica, no banco: sao
 * dados configuraveis no painel, nunca variaveis de build.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->id();

            $table->string('seo_title')->nullable();
            $table->string('seo_description', 255)->nullable();
            $table->foreignId('seo_image_id')->nullable()->constrained('media')->restrictOnDelete();

            $table->string('google_analytics_id')->nullable();
            $table->string('google_tag_manager_id')->nullable();
            $table->string('meta_pixel_id')->nullable();

            $table->timestamps();
        });

        DB::table('configuracoes')->insert([
            'seo_title' => 'Dra. Marina Mariz — Ginecologia e Obstetrícia',
            'seo_description' => 'Acompanhamento em ginecologia e obstetrícia em Belo Horizonte, com informação clara e cuidado próximo em cada fase.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes');
    }
};
