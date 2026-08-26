<?php

use Filament\Actions\Exports\Models\Export;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Cron do hPanel chama `schedule:run` a cada 5 minutos (doc 08). Nao ha
 * daemon: tudo abaixo roda em processo curto e sai.
 */

// Entrega o e-mail de aviso dos envios pendentes.
Schedule::command('outbox:processar')
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Exportacoes CSV do Filament: a fila `database` gera o arquivo.
Schedule::command('queue:work --stop-when-empty --tries=3')
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Backup semanal dos contatos numa pasta fora do laravel/ (ultimo recurso
// se o sistema sumir). Lock com expiracao: processo morto no meio nao pode
// travar o comando para sempre.
Schedule::command('backup:contatos')
    ->weeklyOn(1, '03:00')
    ->withoutOverlapping(30);

// Acompanhamento mensal por e-mail do que os backups semanais gravaram.
Schedule::command('backup:relatorio')
    ->monthlyOn(1, '06:00')
    ->withoutOverlapping(10);

// CSV de formulario nao fica no disco: some 24h depois de ficar pronto
// (doc 08). Registro e arquivo saem juntos.
Schedule::call(function (): void {
    Export::query()
        ->where('created_at', '<', now()->subDay())
        ->each(function (Export $export): void {
            $export->deleteFileDirectory();
            $export->delete();
        });
})->hourly()->name('limpar-exportacoes')->withoutOverlapping();
