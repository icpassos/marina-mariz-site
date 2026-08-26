<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusMensagem: string implements HasColor, HasLabel
{
    case Nova = 'nova';
    case Lida = 'lida';
    case Respondida = 'respondida';
    case Arquivada = 'arquivada';

    public function getLabel(): string
    {
        return match ($this) {
            self::Nova => 'Nova',
            self::Lida => 'Lida',
            self::Respondida => 'Respondida',
            self::Arquivada => 'Arquivada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Nova => 'danger',
            self::Lida => 'warning',
            self::Respondida => 'success',
            self::Arquivada => 'gray',
        };
    }
}
