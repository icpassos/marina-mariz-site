<?php

namespace App\Providers;

use App\Support\IconesDoPainel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        IconesDoPainel::registrar();

        $this->registrarColunasDeConteudo();
    }

    /**
     * Colunas que todo conteudo do painel tem (doc 00). Em macro para os
     * modulos nao divergirem no nome nem no tipo da mesma coluna.
     */
    private function registrarColunasDeConteudo(): void
    {
        Blueprint::macro('conteudoBase', function (): void {
            /** @var Blueprint $this */
            $this->string('status')->default('rascunho')->index();
            $this->unsignedInteger('position')->default(0)->index();
            // Marco da primeira publicacao, gravado uma vez so. O status
            // atual nao conta essa historia: publicado e devolvido a
            // rascunho ja foi publicado, e isso decide se os arquivos
            // enviados ficam na biblioteca quando o item e excluido.
            $this->timestamp('publicado_pela_primeira_vez_em')->nullable();
            // Arquivo em uso nao some da biblioteca sem aviso: apagar aqui
            // esvaziaria a imagem do item em silencio (doc 05).
            $this->foreignId('image_id')->nullable()->constrained('media')->restrictOnDelete();
        });

        Blueprint::macro('conteudoSeo', function (): void {
            /** @var Blueprint $this */
            $this->string('seo_title')->nullable();
            $this->string('seo_description', 255)->nullable();
            $this->foreignId('seo_image_id')->nullable()->constrained('media')->restrictOnDelete();
        });
    }
}
