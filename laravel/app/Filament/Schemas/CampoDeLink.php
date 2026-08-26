<?php

namespace App\Filament\Schemas;

/**
 * Campo de endereco no painel: clicou no campo vazio, o `https://` ja
 * aparece com o cursor depois dele.
 *
 * Todo link do painel e obrigado a comecar com `https://` — deixar isso para
 * quem preenche so rendia erro de validacao no fim do formulario.
 */
class CampoDeLink
{
    /** @return array<string, string> */
    public static function comecaEmHttps(): array
    {
        return [
            // `input` e o evento que o wire:model escuta: sem disparar ele, o
            // texto apareceria na tela e o servidor continuaria com o campo vazio.
            'onfocus' => "if (! this.value) { this.value = 'https://';"
                ." this.dispatchEvent(new Event('input')); this.setSelectionRange(8, 8) }",
        ];
    }
}
