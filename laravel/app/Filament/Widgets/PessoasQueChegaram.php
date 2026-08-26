<?php

namespace App\Filament\Widgets;

use App\Enums\Icone;
use App\Enums\StatusMensagem;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Mensagens\MensagemResource;
use App\Models\BackupDeContatos;
use App\Models\Lead;
use App\Models\Mensagem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Quem chegou pelo site e ainda espera resposta (doc 00, TELA INICIAL).
 *
 * Cada numero so aparece para quem pode abrir a lista correspondente:
 * mostrar contagem de mensagem a quem nao pode ler mensagem ja e vazamento.
 *
 * A terceira coluna leva o alarme do backup semanal: o cron da hospedagem
 * e calado, e este e o quadro que a falha alcanca sem ninguem abrir log.
 */
class PessoasQueChegaram extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    /** Recorte de "leads novos" pedido pela spec. */
    public const DIAS_DE_LEAD_NOVO = 7;

    public static function mensagensNaoLidas(): int
    {
        // Mesmo recorte da lista: registro sem submission_id nao existe para o painel.
        return MensagemResource::escopoVisivel(Mensagem::query())
            ->where('status', StatusMensagem::Nova)
            ->count();
    }

    public static function leadsNovos(): int
    {
        return Lead::where('created_at', '>=', now()->subDays(self::DIAS_DE_LEAD_NOVO))->count();
    }

    /** Lista de mensagens ja filtrada por "Nova". */
    public static function urlDasMensagens(): string
    {
        return MensagemResource::getUrl('index', [
            'tableFilters' => ['status' => ['value' => StatusMensagem::Nova->value]],
        ]);
    }

    /** Lista de leads ja filtrada pelos ultimos 7 dias. */
    public static function urlDosLeads(): string
    {
        return LeadResource::getUrl('index', [
            'tableFilters' => ['periodo' => [
                'de' => now()->subDays(self::DIAS_DE_LEAD_NOVO)->toDateString(),
            ]],
        ]);
    }

    /**
     * Estado do backup semanal. Publico porque e o que o teste verifica.
     *
     * @return array{valor: string, descricao: string, cor: string}
     */
    public static function resumoDoBackup(): array
    {
        $ultimo = BackupDeContatos::ultimo();

        if ($ultimo === null) {
            return [
                'valor' => 'Nunca executado',
                'descricao' => 'Nenhum backup dos contatos foi gravado ainda. Confira o cron no hPanel.',
                'cor' => 'danger',
            ];
        }

        $quando = $ultimo->executado_em->format('d/m/Y H:i');

        if (BackupDeContatos::atrasado()) {
            return [
                'valor' => $quando,
                'descricao' => 'Mais de '.BackupDeContatos::DIAS_ATE_O_ALERTA
                    .' dias sem backup. Confira o cron no hPanel.',
                'cor' => 'danger',
            ];
        }

        return [
            'valor' => $quando,
            'descricao' => $ultimo->total().' registro(s) em '.$ultimo->caminho,
            'cor' => 'success',
        ];
    }

    public static function canView(): bool
    {
        $usuario = auth()->user();

        return ($usuario?->isAdministrator() ?? false)
            || ($usuario?->can('viewAny', Mensagem::class) ?? false)
            || ($usuario?->can('viewAny', Lead::class) ?? false);
    }

    protected function getStats(): array
    {
        $usuario = auth()->user();
        $stats = [];

        if ($usuario?->can('viewAny', Mensagem::class)) {
            $naoLidas = self::mensagensNaoLidas();

            $stats[] = Stat::make('Mensagens não lidas', $naoLidas)
                ->description($naoLidas > 0 ? 'Abrir a caixa de entrada' : 'Nada esperando resposta')
                ->icon(Icone::ChatsCircle)
                ->color($naoLidas > 0 ? 'danger' : 'success')
                ->url(self::urlDasMensagens());
        }

        if ($usuario?->can('viewAny', Lead::class)) {
            $novos = self::leadsNovos();

            $stats[] = Stat::make('Leads novos (7 dias)', $novos)
                ->description($novos > 0 ? 'Ver quem baixou material' : 'Nenhum download nos últimos 7 dias')
                ->icon(Icone::UserPlus)
                ->color($novos > 0 ? 'success' : 'gray')
                ->url(self::urlDosLeads());
        }

        // Terceira coluna: backup dos contatos e assunto de Administrador.
        if ($usuario?->isAdministrator()) {
            $backup = self::resumoDoBackup();

            $stats[] = Stat::make('Último backup dos contatos', $backup['valor'])
                ->description($backup['descricao'])
                ->icon(Icone::Database)
                ->color($backup['cor']);
        }

        return $stats;
    }
}
