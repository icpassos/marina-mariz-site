<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** Formato de evento. Presencial exige o bloco Local; online nao (doc 01). */
enum EventFormat: string implements HasLabel
{
    case Presencial = 'presencial';
    case Online = 'online';

    public function getLabel(): string
    {
        return match ($this) {
            self::Presencial => 'Presencial',
            self::Online => 'Online',
        };
    }
}
