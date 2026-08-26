<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\ManageRecords;

class ManageCategories extends ManageRecords
{
    protected static string $resource = CategoryResource::class;

    /** De onde vem e para onde vai esta seção — a Marina não acompanhou a construção. */
    protected ?string $subheading = 'As abas de /blog são estas categorias, na ordem definida aqui. Categoria sem post publicado não vira aba, e excluir categoria não exclui post: ele passa para “Sem categoria”.';
}
