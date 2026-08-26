<?php

namespace App\Filament\Widgets;

use App\Enums\EducationType;
use App\Enums\Icone;
use App\Filament\Resources\EducationItems\EducationItemResource;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\EducationItem;
use App\Models\Lead;
use App\Models\Post;
use App\Models\User;
use Filament\Widgets\Widget;

/**
 * Mapa do painel (doc 00, TELA INICIAL): quem entra ve, de uma vez, para
 * onde pode ir — os seis tipos de Educacao, o blog, usuarios e leads.
 *
 * Cada atalho passa pela Policy do modulo. Nao e ocultacao visual: o que
 * nao aparece aqui tambem e negado no servidor pela mesma regra.
 */
class AtalhosDoPainel extends Widget
{
    protected string $view = 'filament.widgets.atalhos-do-painel';

    /** Acima de tudo: e o indice da casa, nao o rodape dela. */
    protected static ?int $sort = -4;

    protected int|string|array $columnSpan = 'full';

    /** @return array<int, array{rotulo: string, url: string, icone: Icone}> */
    public static function atalhos(): array
    {
        $usuario = auth()->user();
        $atalhos = [];

        if ($usuario?->can('create', Post::class)) {
            $atalhos[] = [
                'rotulo' => 'Novo post',
                'url' => PostResource::getUrl('create'),
                'icone' => Icone::NotePencil,
            ];
        }

        if ($usuario?->can('create', EducationItem::class)) {
            foreach (EducationType::cases() as $tipo) {
                $atalhos[] = [
                    'rotulo' => self::rotuloDe($tipo),
                    // Ja abre o formulario com o tipo escolhido, como as
                    // entradas do menu fazem.
                    'url' => EducationItemResource::getUrl('create', ['tipo' => $tipo->value]),
                    'icone' => $tipo->icone(),
                ];
            }
        }

        if ($usuario?->can('viewAny', Lead::class)) {
            $atalhos[] = [
                'rotulo' => 'Ver leads',
                'url' => LeadResource::getUrl('index'),
                'icone' => Icone::UserPlus,
            ];
        }

        if ($usuario?->can('create', User::class)) {
            $atalhos[] = [
                'rotulo' => 'Novo usuário',
                'url' => UserResource::getUrl('create'),
                'icone' => Icone::UserCircle,
            ];
        }

        return $atalhos;
    }

    public static function canView(): bool
    {
        return self::atalhos() !== [];
    }

    /** Rotulo curto: o cartao do tipo ja diz o resto. */
    private static function rotuloDe(EducationType $tipo): string
    {
        return match ($tipo) {
            EducationType::Livro => 'Novo livro',
            EducationType::Ebook => 'Novo e-book',
            EducationType::Curso => 'Novo curso',
            EducationType::Evento => 'Novo evento',
            EducationType::Material => 'Novo material',
            EducationType::Formacao => 'Nova formação',
        };
    }
}
