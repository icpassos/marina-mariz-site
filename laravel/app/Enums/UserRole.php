<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    case Administrator = 'administrator';
    case Editor = 'editor';

    public function getLabel(): string
    {
        return match ($this) {
            self::Administrator => 'Administrador',
            self::Editor => 'Editor',
        };
    }
}
