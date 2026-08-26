<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();

            // Excluir a categoria nao leva o post junto: ele passa a
            // "Sem categoria", que e a ausencia de categoria (doc 02).
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->text('description')->nullable();

            // O RichEditor grava JSON estruturado, nao HTML (doc 08).
            $table->json('content')->nullable();

            $table->string('author')->default('Dra. Marina Mariz');

            // Instante UTC. Data futura com status publicado e o que a tela
            // mostra como "Agendado" — nao existe quarto status no banco.
            $table->timestamp('published_at')->nullable();

            $table->string('closing_cta')->nullable();

            // Só existem com o CTA "baixar material gratuito".
            $table->text('material_description')->nullable();
            $table->foreignId('material_media_id')->nullable()->constrained('media')->restrictOnDelete();

            // Só existe com o CTA "ouvir episódio do podcast".
            $table->string('episode_url')->nullable();

            $table->conteudoBase();
            $table->conteudoSeo();

            $table->softDeletes();
            $table->timestamps();

            // Recorte da listagem publica e da busca do site.
            $table->index(['status', 'published_at']);
        });

        Schema::create('post_related', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('related_post_id')->constrained('posts')->cascadeOnDelete();
            $table->primary(['post_id', 'related_post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_related');
        Schema::dropIfExists('posts');
    }
};
