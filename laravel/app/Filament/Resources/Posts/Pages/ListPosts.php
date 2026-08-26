<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Enums\Icone;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    /** De onde vem e para onde vai esta seção — a Marina não acompanhou a construção. */
    protected ?string $subheading = 'Os posts de /blog e de /blog/nome-do-post. Publicado com data futura, o post entra no site sozinho quando a data chega.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Novo post'),
            self::contadorSemCategoria(),
        ];
    }

    /**
     * Sem esse contador um post fica fora da navegação por categoria e
     * ninguém descobre por meses (doc 02). O botão leva direto ao filtro.
     */
    private static function contadorSemCategoria(): Action
    {
        $quantos = Post::whereNull('category_id')->count();

        return Action::make('semCategoria')
            ->label($quantos.' '.str('post')->plural($quantos).' sem categoria')
            ->icon(Icone::Warning)
            ->color('warning')
            ->link()
            ->visible($quantos > 0)
            ->url(PostResource::getUrl('index', [
                'tableFilters' => ['categoria' => ['value' => PostsTable::SEM_CATEGORIA]],
            ]));
    }
}
