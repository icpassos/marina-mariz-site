<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** Formato de curso e de formacao profissional (doc 01). */
enum CourseFormat: string implements HasLabel
{
    case OnlineAoVivo = 'online_ao_vivo';
    case Gravado = 'gravado';
    case Presencial = 'presencial';
    case Hibrido = 'hibrido';

    public function getLabel(): string
    {
        return match ($this) {
            self::OnlineAoVivo => 'Online ao vivo',
            self::Gravado => 'Gravado',
            self::Presencial => 'Presencial',
            self::Hibrido => 'Híbrido',
        };
    }
}
