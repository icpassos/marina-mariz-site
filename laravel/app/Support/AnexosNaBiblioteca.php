<?php

namespace App\Support;

use App\Models\Media;
use Filament\Forms\Components\RichEditor\FileAttachmentProviders\Contracts\FileAttachmentProvider;
use Filament\Forms\Components\RichEditor\RichContentAttribute;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Imagem colada no editor rico entra na Biblioteca de Mídia (doc 02).
 *
 * O que o conteúdo guarda é o ID de um registro de `media`, não um caminho
 * vindo do navegador: assim o renderer só serve arquivo que existe na
 * biblioteca e não há upload público paralelo.
 */
class AnexosNaBiblioteca implements FileAttachmentProvider
{
    protected ?RichContentAttribute $attribute = null;

    public function attribute(RichContentAttribute $attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getFileAttachmentUrl(mixed $file): ?string
    {
        return Media::find($file)?->url();
    }

    public function saveUploadedFileAttachment(TemporaryUploadedFile $file): mixed
    {
        return (string) Media::daUpload($file)->getKey();
    }

    /**
     * IDs de mídia colados no corpo. O TipTap guarda a imagem como um nó
     * `image` com `attrs.id` — é o único vínculo que existe, não há chave
     * estrangeira apontando para a biblioteca.
     *
     * @return array<int, int>
     */
    public static function idsEm(mixed $conteudo): array
    {
        if (! is_array($conteudo)) {
            return [];
        }

        $ids = ($conteudo['type'] ?? null) === 'image' && filled($conteudo['attrs']['id'] ?? null)
            ? [(int) $conteudo['attrs']['id']]
            : [];

        foreach ($conteudo['content'] ?? [] as $no) {
            $ids = [...$ids, ...self::idsEm($no)];
        }

        return $ids;
    }

    public function getDefaultFileAttachmentVisibility(): ?string
    {
        return 'private';
    }

    public function isExistingRecordRequiredToSaveNewFileAttachments(): bool
    {
        return false;
    }

    /**
     * Não apaga nada. A biblioteca é compartilhada — a mesma imagem pode
     * estar em outro post ou item de Educação, e tirá-la de um post não
     * autoriza sumir com o arquivo.
     *
     * @param  array<mixed>  $exceptIds
     */
    public function cleanUpFileAttachments(array $exceptIds): void {}
}
