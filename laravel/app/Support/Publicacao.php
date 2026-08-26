<?php

namespace App\Support;

use App\Enums\ContentStatus;
use Closure;
use Filament\Schemas\Components\Utilities\Get;

/**
 * "Obrigatorio para PUBLICAR, nao para salvar" (docs 01 e 02).
 *
 * Rascunho guarda uma ideia pela metade com so o titulo. A exigencia
 * aparece quando o item vai ao ar, e e conferida no servidor tambem —
 * o estado que o Livewire manda nao e confiavel.
 */
class Publicacao
{
    /** Para `->required()` de um campo exigido apenas na publicacao. */
    public static function exigido(): Closure
    {
        return fn (Get $get): bool => self::vaiPublicar($get('status'));
    }

    /**
     * Mesma exigencia, so que condicionada a outro campo estar visivel:
     * Local nao e cobrado em evento online, link de compra nao e cobrado
     * em e-book gratuito.
     */
    public static function exigidoQuando(Closure $condicao): Closure
    {
        return fn (Get $get): bool => self::vaiPublicar($get('status')) && $condicao($get);
    }

    public static function vaiPublicar(mixed $status): bool
    {
        return $status === ContentStatus::Publicado
            || $status === ContentStatus::Publicado->value;
    }

    /**
     * Lista o que ainda falta para publicar, para a tela dizer o motivo
     * em vez de so desabilitar o botao.
     *
     * @param  array<string, string>  $exigidos  campo => rotulo
     * @param  array<string, mixed>  $dados
     * @return array<int, string>
     */
    public static function pendencias(array $exigidos, array $dados): array
    {
        $faltando = [];

        foreach ($exigidos as $campo => $rotulo) {
            if (blank($dados[$campo] ?? null)) {
                $faltando[] = $rotulo;
            }
        }

        return $faltando;
    }
}
