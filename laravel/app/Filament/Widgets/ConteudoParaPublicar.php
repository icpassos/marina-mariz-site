<?php

namespace App\Filament\Widgets;

use App\Enums\ContentStatus;
use App\Enums\EducationType;
use App\Enums\Icone;
use App\Filament\Resources\EducationItems\EducationItemResource;
use App\Filament\Resources\Posts\PostResource;
use App\Models\EducationItem;
use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * O que esta pela metade e o que vem pela frente (doc 00, TELA INICIAL).
 *
 * Rascunho aparece em dois numeros, um por modulo, porque Blog e Educacao
 * sao listas separadas: um numero somado nao teria para onde levar.
 */
class ConteudoParaPublicar extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    /** @return array{blog: int, educacao: int, total: int} */
    public static function rascunhos(): array
    {
        $blog = Post::where('status', ContentStatus::Rascunho)->count();
        $educacao = EducationItem::where('status', ContentStatus::Rascunho)->count();

        return ['blog' => $blog, 'educacao' => $educacao, 'total' => $blog + $educacao];
    }

    /** Evento publicado que ainda vai acontecer, o mais proximo primeiro. */
    public static function proximoEvento(): ?EducationItem
    {
        return EducationItem::query()
            ->where('type', EducationType::Evento)
            ->publicado()
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->first();
    }

    /** O filtro de status do Blog aceita varios valores; o de Educacao, um so. */
    public static function urlDosRascunhosDoBlog(): string
    {
        return PostResource::getUrl('index', [
            'tableFilters' => ['status' => ['values' => [ContentStatus::Rascunho->value]]],
        ]);
    }

    public static function urlDosRascunhosDeEducacao(): string
    {
        return EducationItemResource::getUrl('index', [
            'tableFilters' => ['status' => ['value' => ContentStatus::Rascunho->value]],
        ]);
    }

    public static function canView(): bool
    {
        $usuario = auth()->user();

        return ($usuario?->can('viewAny', Post::class) ?? false)
            || ($usuario?->can('viewAny', EducationItem::class) ?? false);
    }

    protected function getStats(): array
    {
        $usuario = auth()->user();
        $rascunhos = self::rascunhos();
        $stats = [];

        if ($usuario?->can('viewAny', Post::class)) {
            $stats[] = Stat::make('Rascunhos no blog', $rascunhos['blog'])
                ->description($rascunhos['blog'] > 0 ? 'Pendentes de publicação' : 'Nenhum post pendente')
                ->icon(Icone::NotePencil)
                ->color($rascunhos['blog'] > 0 ? 'warning' : 'gray')
                ->url(self::urlDosRascunhosDoBlog());
        }

        if ($usuario?->can('viewAny', EducationItem::class)) {
            $stats[] = Stat::make('Rascunhos em Educação', $rascunhos['educacao'])
                ->description($rascunhos['educacao'] > 0 ? 'Pendentes de publicação' : 'Nenhum item pendente')
                ->icon(Icone::GraduationCap)
                ->color($rascunhos['educacao'] > 0 ? 'warning' : 'gray')
                ->url(self::urlDosRascunhosDeEducacao());

            $evento = self::proximoEvento();

            $stats[] = Stat::make(
                'Próximo evento',
                $evento?->starts_at->format('d/m/Y H:i') ?? 'Nenhum agendado',
            )
                // Sem evento futuro o quadro diz isso; nunca fica em branco.
                ->description($evento?->title ?? 'Nenhum evento publicado com data futura.')
                ->icon(Icone::CalendarCheck)
                ->color($evento ? 'success' : 'gray')
                ->url($evento
                    ? EducationItemResource::getUrl('edit', ['record' => $evento])
                    : EducationItemResource::getUrl('index', ['tipo' => EducationType::Evento->value]));
        }

        return $stats;
    }
}
