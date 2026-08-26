<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Link;
use Illuminate\Database\Seeder;

/**
 * Os botoes com que a pagina /links nasce (doc 09).
 *
 * Sao caminhos deste site, nunca endereco de fora: link de fora muda de dono
 * e de campanha, e quem decide isso e o painel. Rodar de novo nao duplica nem
 * sobrescreve o que ja foi editado — casa pelo endereco.
 */
class LinksPadraoSeeder extends Seeder
{
    /** @var array<int, array{title: string, url: string, description: string}> */
    private const PADRAO = [
        ['title' => 'Agendar consulta', 'url' => '/contato', 'description' => 'Consultório em Belo Horizonte e atendimento particular.'],
        ['title' => 'Materiais gratuitos', 'url' => '/educacao/materiais-gratuitos', 'description' => 'Guias e checklists para baixar agora.'],
        ['title' => 'E-books', 'url' => '/educacao/ebooks', 'description' => 'Conteúdo digital sobre gestação, parto e puerpério.'],
        ['title' => 'Cursos', 'url' => '/educacao/cursos', 'description' => 'Formação para gestantes e famílias.'],
        ['title' => 'Sem Neura Podcast', 'url' => '/podcast', 'description' => 'Os episódios e onde ouvir.'],
        ['title' => 'Blog', 'url' => '/blog', 'description' => 'Artigos baseados em evidência.'],
        ['title' => 'Site completo', 'url' => '/', 'description' => 'Tudo sobre o consultório e o trabalho da Dra. Marina.'],
    ];

    public function run(): void
    {
        foreach (self::PADRAO as $ordem => $link) {
            Link::firstOrCreate(
                ['url' => $link['url']],
                [
                    'title' => $link['title'],
                    'description' => $link['description'],
                    'position' => $ordem,
                    'status' => ContentStatus::Publicado,
                    'publicado_pela_primeira_vez_em' => now(),
                ],
            );
        }
    }
}
