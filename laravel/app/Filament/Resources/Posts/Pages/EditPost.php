<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Actions\VerNoSite;
use App\Filament\Concerns\PublicaOuSalvaRascunho;
use App\Filament\Resources\Posts\Actions\AcoesDePost;
use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    use PublicaOuSalvaRascunho;

    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Exportação usa a última versão salva: o que ainda está aberto
            // no formulário não entra (doc 08).
            VerNoSite::post(),
            AcoesDePost::baixarPdf(),
            AcoesDePost::baixarWord(),
            AcoesDePost::arquivar(),
            AcoesDePost::desarquivar(),
            DeleteAction::make()->label('Enviar à lixeira'),
            RestoreAction::make()->label('Restaurar'),
            ForceDeleteAction::make()->label('Excluir definitivamente'),
        ];
    }
}
