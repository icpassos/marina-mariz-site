<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\Exporter;
use Illuminate\Database\Eloquent\Model;

/**
 * Base das tres exportacoes de formulario (doc 08).
 *
 * Colunas fixas, CSV apenas, disco privado e — o ponto que importa — nenhum
 * valor sai como formula. O Filament nao consulta a Policy registro por
 * registro na exportacao; por isso o recorte vem do `modifyQueryUsing` de
 * cada Resource, alem da Policy que autoriza iniciar e baixar.
 */
abstract class ExportadorPessoas extends Exporter
{
    /** @return array<mixed> */
    public function __invoke(Model $record): array
    {
        return array_map(self::neutralizar(...), parent::__invoke($record));
    }

    /**
     * Valor comecado por `=`, `+`, `-` ou `@` vira formula ao abrir o CSV no
     * Excel ou em outra planilha. Prefixar com apostrofo mantem o texto visivel e
     * mata a execucao.
     */
    public static function neutralizar(mixed $valor): mixed
    {
        if (! is_string($valor) || $valor === '') {
            return $valor;
        }

        return in_array($valor[0], ['=', '+', '-', '@'], strict: true)
            ? "'".$valor
            : $valor;
    }

    /** CSV e so; XLSX nao foi pedido e amplia a superficie de formula. */
    public function getFormats(): array
    {
        return [ExportFormat::Csv];
    }

    /** Disco privado: CSV de formulario nunca fica em `public/`. */
    public function getFileDisk(): string
    {
        return 'local';
    }
}
