<?php

namespace App\Filament\Schemas;

use Filament\Actions\Action;
use Filament\Schemas\Components\Actions;

/**
 * Os botoes de salvar/publicar tambem no topo da coluna da direita, como a
 * caixa "Publicar" do WordPress. O rodape continua onde estava.
 */
class AcoesDoFormulario
{
    public static function noTopo(): Actions
    {
        // A tela de visualizacao usa o mesmo schema e nao tem botao nenhum;
        // sem acoes visiveis o proprio Filament esconde o bloco.
        return Actions::make(fn ($livewire): array => method_exists($livewire, 'getFormActions')
            ? array_map(
                // Mesmo botao do rodape, so sem o atalho de teclado: dois
                // `mod+s` no mesmo formulario disparariam dois envios.
                fn (object $acao): object => $acao instanceof Action ? $acao->keyBindings(null) : $acao,
                $livewire->getFormActions(),
            )
            : [])
            ->key('acoes-do-topo')
            ->fullWidth()
            // Na faixa estreita da direita, tres botoes lado a lado quebram
            // o rotulo em tres linhas. Empilhados, cada um ocupa a largura.
            ->extraAttributes(['class' => 'acoes-empilhadas']);
    }
}
