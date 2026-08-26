<?php

namespace App\Console\Commands;

use App\Filament\Exports\ExportadorPessoas;
use App\Models\BackupDeContatos;
use App\Models\InscricaoNewsletter;
use App\Models\Lead;
use App\Models\Mensagem;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Copia semanal dos contatos em CSV, fora do public_html e fora do laravel/.
 *
 * Ultimo recurso: se o sistema sumir, basta abrir a pasta e achar os CSVs
 * datados. Nada e apagado automaticamente — quem limpa e uma pessoa.
 */
class BackupContatos extends Command
{
    protected $signature = 'backup:contatos {--tudo : Copia tudo, ignorando o marco do backup anterior}';

    protected $description = 'Grava em CSV os contatos que chegaram desde o backup anterior';

    public function handle(): int
    {
        $destino = $this->destino();

        // O recorte sai do marco do ultimo backup, nunca de "ultimos 7 dias":
        // se o cron ficar tres semanas parado, esta execucao leva as tres.
        $desde = $this->option('tudo') ? null : BackupDeContatos::ultimo()?->executado_em;

        $data = now()->format('Y-m-d');
        $contagens = [];
        $arquivos = [];

        foreach ($this->consultas($desde) as $nome => $consulta) {
            $registros = $consulta->get();
            $contagens[$nome] = $registros->count();

            // Semana sem registro daquele tipo nao vira arquivo vazio.
            if ($registros->isEmpty()) {
                continue;
            }

            $arquivo = "{$destino}/{$nome}-{$data}.csv";
            $this->gravarCsv($arquivo, $registros->map($this->linha($nome))->all());
            $arquivos[] = $arquivo;

            $this->info("{$nome}: {$registros->count()} registro(s).");
        }

        if ($arquivos === []) {
            Log::info('backup:contatos — nenhum contato novo desde o backup anterior.', ['desde' => (string) $desde]);
            $this->info('Nenhum contato novo. Nada a gravar.');
        }

        // Marco gravado mesmo sem arquivo: e dele que sai o recorte da
        // proxima execucao e o alerta do painel.
        BackupDeContatos::create([
            'executado_em' => now(),
            'caminho' => $destino,
            ...$contagens,
        ]);

        return self::SUCCESS;
    }

    /**
     * Pasta configurada; se nao der para gravar nela, cai no storage/ e diz
     * no log. Falhar calado seria pior do que backup nenhum.
     */
    private function destino(): string
    {
        $configurado = (string) config('pessoas.backup_contatos_path');

        if (filled($configurado) && $this->prepararPasta($configurado)) {
            return $configurado;
        }

        $fallback = storage_path('app/private/backups-contatos');

        Log::warning('backup:contatos — pasta configurada indisponivel, usando o fallback.', [
            'configurada' => $configurado,
            'fallback' => $fallback,
        ]);
        $this->warn("Pasta {$configurado} indisponível. Gravando em {$fallback}.");

        $this->prepararPasta($fallback);

        return $fallback;
    }

    /** Cria a pasta em 0700: dado pessoal em disco compartilhado. */
    private function prepararPasta(string $pasta): bool
    {
        if (! is_dir($pasta) && ! @mkdir($pasta, 0700, true)) {
            return false;
        }

        @chmod($pasta, 0700);

        return is_writable($pasta);
    }

    /** @return array<string, Builder> */
    private function consultas(?Carbon $desde): array
    {
        return [
            'mensagens' => Mensagem::query()
                ->when($desde, fn (Builder $q) => $q->where('created_at', '>', $desde))
                ->orderBy('id'),

            // Lead antigo com download novo tambem e contato novo da semana.
            'leads' => Lead::query()
                ->with('downloads')
                ->when($desde, fn (Builder $q) => $q->where(fn (Builder $ou) => $ou
                    ->where('created_at', '>', $desde)
                    ->orWhereHas('downloads', fn (Builder $d) => $d->where('created_at', '>', $desde))))
                ->orderBy('id'),

            'newsletter' => InscricaoNewsletter::query()
                ->when($desde, fn (Builder $q) => $q->where('created_at', '>', $desde))
                ->orderBy('id'),
        ];
    }

    /**
     * Colunas do backup: tudo que reconstroi o registro, menos o IP — que
     * nem em claro existe no banco.
     */
    private function linha(string $nome): callable
    {
        return match ($nome) {
            'mensagens' => fn (Mensagem $m): array => [
                'id' => $m->id,
                'submission_id' => $m->submission_id,
                'criado_em' => $m->created_at?->toIso8601String(),
                'atualizado_em' => $m->updated_at?->toIso8601String(),
                'origem' => $m->origem->value,
                'nome' => $m->nome,
                'email' => $m->email,
                'whatsapp' => $m->whatsapp,
                'texto' => $m->texto,
                'profissao' => $m->profissao,
                'registro_profissional' => $m->registro_profissional,
                'instituicao' => $m->instituicao,
                'modalidade' => $m->modalidade,
                'status' => $m->status->value,
                'anotacao' => $m->anotacao,
                'politica_versao' => $m->politica_versao,
            ],

            'leads' => fn (Lead $l): array => [
                'id' => $l->id,
                'criado_em' => $l->created_at?->toIso8601String(),
                'atualizado_em' => $l->updated_at?->toIso8601String(),
                'nome' => $l->nome,
                'email' => $l->email,
                'telefone' => $l->telefone,
                'eh_cliente' => $l->eh_cliente ? 'sim' : 'não',
                'politica_versao' => $l->politica_versao,
                'materiais' => $l->downloads
                    ->map(fn ($d): string => $d->origem.' ('.$d->created_at?->toDateString().')')
                    ->implode(' | '),
            ],

            default => fn (InscricaoNewsletter $i): array => [
                'id' => $i->id,
                'submission_id' => $i->submission_id,
                'criado_em' => $i->created_at?->toIso8601String(),
                'atualizado_em' => $i->updated_at?->toIso8601String(),
                'nome' => $i->nome,
                'email' => $i->email,
                'origem' => $i->origem,
                'status' => $i->status->value,
                'descadastro_token' => $i->descadastro_token,
                'descadastrado_em' => $i->descadastrado_em?->toIso8601String(),
                'politica_versao' => $i->politica_versao,
            ],
        };
    }

    /** @param  list<array<string, mixed>>  $linhas */
    private function gravarCsv(string $arquivo, array $linhas): void
    {
        $handle = fopen($arquivo, 'w');
        // 0600 antes de escrever: dado pessoal em disco compartilhado.
        chmod($arquivo, 0600);

        fputcsv($handle, array_keys($linhas[0]));

        foreach ($linhas as $linha) {
            // Mesma neutralizacao da exportacao do painel: valor comecado por
            // `=`, `+`, `-` ou `@` nao pode virar formula no Excel.
            fputcsv($handle, array_map(ExportadorPessoas::neutralizar(...), array_values($linha)));
        }

        fclose($handle);
    }
}
