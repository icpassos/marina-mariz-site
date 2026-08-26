<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * O CTA que fecha o post. São quatro, fixos: quem publica escolhe um no
 * dropdown e não cria variação nova (doc 02).
 *
 * Newsletter e Consulta não guardam nada — título, descrição e botão são
 * fixos no site. Só Material e Podcast pedem campo no painel.
 */
enum PostCta: string implements HasLabel
{
    case Newsletter = 'newsletter';
    case Consulta = 'consulta';
    case Material = 'material';
    case Podcast = 'podcast';

    public function getLabel(): string
    {
        return match ($this) {
            self::Newsletter => 'Assinar newsletter',
            self::Consulta => 'Agendar consulta',
            self::Material => 'Baixar material gratuito',
            self::Podcast => 'Ouvir o episódio do podcast',
        };
    }

    /** O Livewire manda ora o enum, ora a string; os dois viram enum aqui. */
    public static function de(mixed $valor): ?self
    {
        return $valor instanceof self ? $valor : self::tryFrom((string) $valor);
    }
}
