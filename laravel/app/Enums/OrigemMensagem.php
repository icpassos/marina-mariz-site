<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Os dois formularios que caem na caixa de Mensagens (doc 04). Lista
 * fechada: a origem nunca vem livre do navegador.
 */
enum OrigemMensagem: string implements HasColor, HasLabel
{
    case Contato = 'contato';
    case FormacaoProfissional = 'formacao_profissional';

    public function getLabel(): string
    {
        return match ($this) {
            self::Contato => 'Contato',
            self::FormacaoProfissional => 'Formação profissional',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Contato => 'gray',
            self::FormacaoProfissional => 'info',
        };
    }
}
