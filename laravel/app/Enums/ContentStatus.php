<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Os tres estados de todo conteudo do painel (doc 00).
 *
 * "Agendado" nao existe aqui: e exibicao derivada de Publicado com data
 * de publicacao no futuro, nao um quarto valor no banco.
 */
enum ContentStatus: string implements HasColor, HasLabel
{
    case Rascunho = 'rascunho';
    case Publicado = 'publicado';
    case Arquivado = 'arquivado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Rascunho => 'Rascunho',
            self::Publicado => 'Publicado',
            self::Arquivado => 'Arquivado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Rascunho => 'gray',
            self::Publicado => 'success',
            self::Arquivado => 'warning',
        };
    }
}
