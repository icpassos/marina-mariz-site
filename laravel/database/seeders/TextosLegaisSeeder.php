<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\TextoLegal;
use Illuminate\Database\Seeder;

/**
 * Politica de Privacidade e Termos de Uso (doc 03).
 *
 * Os dois nascem PUBLICADOS, com os dados que o site ja tem: nome, CRM,
 * consultorio, e-mail e telefone. Uma pagina legal em branco e pior do que
 * um texto-padrao — o visitante tem direito de saber o que e coletado, e a
 * LGPD nao espera revisao. O texto continua editavel no painel, e publicar
 * de novo troca o que esta no ar.
 *
 * Nao ha CNPJ nem razao social porque nao existem no cadastro: o
 * controlador declarado e a propria medica, pelo CRM. Se houver pessoa
 * juridica, e so trocar esse trecho no painel.
 *
 * Idempotente: rodar de novo nao sobrescreve texto ja editado.
 */
class TextosLegaisSeeder extends Seeder
{
    public function run(): void
    {
        foreach (TextoLegal::CHAVES as $chave => $titulo) {
            $texto = TextoLegal::firstOrNew(['chave' => $chave]);

            if (filled($texto->conteudo)) {
                continue;
            }

            $texto->fill([
                'titulo' => $texto->titulo ?? $titulo,
                'conteudo' => file_get_contents(__DIR__."/textos-legais/{$chave}.html"),
                'status' => ContentStatus::Publicado,
            ]);

            // `guardar` copia titulo e conteudo para a versao no ar e carimba
            // a data — e o mesmo caminho que o botao "Publicar" usa.
            $texto->guardar($texto->getDirty());
        }
    }
}
