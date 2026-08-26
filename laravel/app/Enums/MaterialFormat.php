<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** Formato do material gratuito (doc 01). */
enum MaterialFormat: string implements HasLabel
{
    case Pdf = 'pdf';
    case Checklist = 'checklist';
    case Planilha = 'planilha';
    case Video = 'video';
    case Audio = 'audio';
    case Outros = 'outros';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pdf => 'PDF',
            self::Checklist => 'Checklist',
            self::Planilha => 'Planilha',
            self::Video => 'Vídeo',
            self::Audio => 'Áudio',
            self::Outros => 'Outros',
        };
    }
}
