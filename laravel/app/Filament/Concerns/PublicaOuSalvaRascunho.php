<?php

namespace App\Filament\Concerns;

use App\Enums\ContentStatus;
use App\Support\Publicacao;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;

/**
 * O rodape do formulario decide o status, como no WordPress: "Publicar"
 * publica de verdade e "Salvar como rascunho" guarda pela metade. Antes o
 * botao unico gravava sempre o que estava no select — quem criava um item
 * novo saia da tela achando que tinha publicado.
 *
 * Tambem deixa o schema alcancar esses botoes para repeti-los no topo da
 * coluna da direita: um lugar so decide o que eles fazem.
 */
trait PublicaOuSalvaRascunho
{
    /** @return array<Action|ActionGroup> */
    public function getFormActions(): array
    {
        $acoes = parent::getFormActions();

        // Publicado ou arquivado mantem o "Salvar" de sempre: mudar o status
        // de um item que ja saiu do rascunho e decisao do select.
        if (! $this->estaEmRascunho()) {
            return $acoes;
        }

        // O primeiro e o botao de enviar o formulario, que aqui ja aponta
        // para `publicarRegistro`: so troca o rotulo, continua um `submit`,
        // entao Enter no teclado faz o mesmo que o clique.
        $publicar = array_shift($acoes)->label('Publicar');

        return [
            $publicar,

            Action::make('salvarRascunho')
                ->label('Salvar como rascunho')
                ->color('gray')
                ->action('salvarRascunho'),

            $this->getCancelFormAction(),
        ];
    }

    /** Enviar o formulario em rascunho publica; e o que o botao promete. */
    protected function getSubmitFormLivewireMethodName(): string
    {
        return $this->estaEmRascunho() ? 'publicarRegistro' : parent::getSubmitFormLivewireMethodName();
    }

    public function publicarRegistro(): void
    {
        $this->data['status'] = ContentStatus::Publicado->value;

        // Post exige data de publicacao. Sem isto o botao devolveria erro num
        // campo que ninguem abriu; "Publicar" quer dizer publicar agora.
        if (array_key_exists('published_at', $this->data ?? []) && blank($this->data['published_at'])) {
            $this->data['published_at'] = now();
        }

        $this->gravar();
    }

    public function salvarRascunho(): void
    {
        $this->data['status'] = ContentStatus::Rascunho->value;

        $this->gravar();
    }

    /** Tela de criar grava com `create()`; a de editar, com `save()`. */
    private function gravar(): void
    {
        method_exists($this, 'create') ? $this->create() : $this->save();
    }

    /**
     * Status ainda em branco conta como rascunho: no formulario de Educacao
     * o select so aparece depois do tipo, e a tela de criar abre sem ele.
     */
    private function estaEmRascunho(): bool
    {
        $status = $this->data['status'] ?? null;

        return ! Publicacao::vaiPublicar($status)
            && $status !== ContentStatus::Arquivado
            && $status !== ContentStatus::Arquivado->value;
    }
}
