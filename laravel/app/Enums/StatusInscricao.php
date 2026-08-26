<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusInscricao: string implements HasColor, HasLabel
{
    case Ativo = 'ativo';
    case Descadastrado = 'descadastrado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Ativo => 'Ativo',
            self::Descadastrado => 'Descadastrado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Ativo => 'success',
            self::Descadastrado => 'gray',
        };
    }
}
