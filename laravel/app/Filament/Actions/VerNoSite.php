<?php

namespace App\Filament\Actions;

use App\Enums\Icone;
use App\Http\Controllers\EducacaoSiteController;
use App\Models\EducationItem;
use App\Models\Link;
use App\Models\Post;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;

/**
 * "Visualizar" e abrir a pagina publica, no site, em outra aba — nao ha
 * tela de leitura dentro do painel: clicar num item leva direto ao editor.
 *
 * Aparece so no que esta no ar. Rascunho, agendado e arquivado nao tem
 * endereco publico, e o botao levaria a um 404.
 */
class VerNoSite
{
    public static function post(): Action
    {
        return self::acao(
            fn (Post $record): bool => $record->estaNoAr(),
            fn (Post $record): string => route('site.blog.post', $record->slug),
        );
    }

    /**
     * O catalogo nao tem pagina por item: cada tipo mora na sua secao, e e
     * para la que o botao leva.
     */
    public static function itemDeEducacao(): Action
    {
        $secoes = array_flip(array_map(
            fn ($tipo): string => $tipo->value,
            EducacaoSiteController::SECOES,
        ));

        return self::acao(
            fn (EducationItem $record): bool => $record->estaPublicado(),
            fn (EducationItem $record): string => route('site.educacao.'.$secoes[$record->type->value]),
        );
    }

    public static function link(): Action
    {
        return self::acao(
            fn (Link $record): bool => $record->estaPublicado(),
            fn (Link $record): string => route('site.links'),
        );
    }

    private static function acao(\Closure $visivel, \Closure $endereco): Action
    {
        return Action::make('verNoSite')
            ->label('Visualizar')
            ->icon(Icone::ArrowUpRight)
            ->color('gray')
            ->visible(fn (Model $record): bool => $visivel($record))
            ->url(fn (Model $record): string => $endereco($record))
            ->openUrlInNewTab();
    }
}
