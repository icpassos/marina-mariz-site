<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** Modalidade da formacao profissional (doc 01). */
enum TrainingModality: string implements HasLabel
{
    case Mentoria = 'mentoria';
    case Supervisao = 'supervisao';
    case InCompany = 'in_company';
    case Palestra = 'palestra';
    case Outra = 'outra';

    public function getLabel(): string
    {
        return match ($this) {
            self::Mentoria => 'Mentoria',
            self::Supervisao => 'Supervisão',
            self::InCompany => 'In company',
            self::Palestra => 'Palestra',
            self::Outra => 'Outra',
        };
    }
}
