<?php

namespace App\Filament\Resources\EducationItems\Pages;

use App\Enums\EducationType;
use App\Filament\Resources\EducationItems\EducationItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListEducationItems extends ListRecords
{
    protected static string $resource = EducationItemResource::class;

    /** Tipo fixado pela entrada do menu; fica na URL para o link ser compartilhável. */
    #[Url]
    public ?string $tipo = null;

    public function tipoAtual(): ?EducationType
    {
        return EducationType::tryFrom((string) $this->tipo);
    }

    public function getTitle(): string
    {
        return $this->tipoAtual()?->plural() ?? 'Educação';
    }

    /** Cada entrada do menu explica o proprio destino no site (doc 01). */
    public function getSubheading(): ?string
    {
        return $this->tipoAtual()?->explicacao()
            ?? 'Catálogo que alimenta as seis páginas de /educacao: livros, e-books, cursos, eventos, materiais gratuitos e formação profissional. Cada item aparece só na página do seu tipo.';
    }

    public function table(Table $table): Table
    {
        return $table->modifyQueryUsing(fn (Builder $query): Builder => $this->tipoAtual()
            ? $query->where('type', $this->tipoAtual())
            : $query);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo item')
                // Vindo de uma entrada do menu o tipo ja vem escolhido;
                // fora dela o formulario pergunta o tipo primeiro.
                ->url(fn (): string => EducationItemResource::getUrl(
                    'create',
                    array_filter(['tipo' => $this->tipo]),
                )),
        ];
    }
}
