<?php

namespace App\Filament\Schemas;

use App\Models\Media;
use App\Rules\ArquivoDeMidia;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Campo de envio das telas de item e de post (docs 01, 02 e 05).
 *
 * O arquivo e enviado onde a pessoa ja esta trabalhando e entra sozinho na
 * Biblioteca de Midia. O estado do campo e o ID do registro de midia, nunca
 * um caminho: por isso substituir o arquivo na Biblioteca troca em todos os
 * lugares que o usam de uma vez, sem abrir item por item.
 */
class EnvioDeMidia
{
    /** Origem aceita para imagem; o servidor converte para WebP (doc 05). */
    public const IMAGENS = ArquivoDeMidia::IMAGENS;

    /** Curinga que vale para qualquer imagem ja guardada na biblioteca. */
    private const QUALQUER_IMAGEM = 'image/*';

    public static function imagem(string $nome, string $rotulo): FileUpload
    {
        return self::campo($nome, $rotulo, self::IMAGENS, self::IMAGENS)
            ->image()
            ->imageResizeMode('contain')
            ->imageResizeTargetWidth((string) ArquivoDeMidia::LARGURA_MAXIMA)
            ->rules([new ArquivoDeMidia])
            ->helperText('Qualquer imagem (JPEG, PNG, WebP, GIF, AVIF): o site converte para WebP com até 1920px. Entra na Biblioteca de Mídia.');
    }

    /** @param  array<int, string>  $extensoes  o que este campo aceita */
    public static function arquivo(string $nome, string $rotulo, array $extensoes): FileUpload
    {
        $tipos = array_intersect_key(ArquivoDeMidia::TIPOS_DE_ARQUIVO, array_flip($extensoes));

        return self::campo(
            $nome,
            $rotulo,
            // No seletor do navegador, so o mime canonico de cada extensao.
            array_column($tipos, 0),
            // Na conferencia do id, todas as variantes que o finfo pode ter lido.
            array_merge(...array_values($tipos)),
        )
            ->rules([new ArquivoDeMidia($extensoes)])
            ->helperText('.'.implode(', .', $extensoes).' — até 50MB. Entra na Biblioteca de Mídia.');
    }

    /**
     * @param  array<int, string>  $noSeletor  mimes oferecidos pelo navegador
     * @param  array<int, string>  $naBiblioteca  mimes aceitos no registro salvo
     */
    private static function campo(string $nome, string $rotulo, array $noSeletor, array $naBiblioteca): FileUpload
    {
        return FileUpload::make($nome)
            ->label($rotulo)
            ->disk('local')
            ->directory('midia')
            ->visibility('private')
            ->acceptedFileTypes($noSeletor)
            ->maxSize((int) (ArquivoDeMidia::TAMANHO_MAXIMO / 1024))
            // O estado e um id de midia, nao um caminho de disco: nao ha o que
            // conferir na pasta nem metadado a ler de la.
            ->fetchFileInformation(false)
            ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => (string) Media::daUpload($file)->getKey())
            ->getUploadedFileUsing(fn (string $file): ?array => self::previa($file))
            // Tirar o arquivo daqui nao o apaga: a biblioteca e compartilhada e
            // o mesmo arquivo pode estar em outro item. Excluir e acao da Midia.
            ->deleteUploadedFileUsing(null)
            ->dehydrateStateUsing(fn (mixed $state): ?int => self::idNaBiblioteca($state, $naBiblioteca));
    }

    /** @return array{name: string, size: int, type: string, url: string}|null */
    private static function previa(string $file): ?array
    {
        $media = Media::find($file);

        return $media instanceof Media
            ? [
                'name' => $media->original_name,
                'size' => $media->size,
                'type' => $media->mime_type,
                'url' => $media->url(),
            ]
            : null;
    }

    /**
     * O que o Livewire manda nao e confiavel: so vira chave estrangeira um id
     * que existe mesmo na biblioteca e cujo arquivo serve para este campo.
     *
     * @param  array<int, string>  $mimes
     */
    private static function idNaBiblioteca(mixed $state, array $mimes): ?int
    {
        $id = is_array($state) ? reset($state) : $state;

        if (! is_numeric($id)) {
            return null;
        }

        $consulta = Media::whereKey($id);

        // Campo de imagem aceita qualquer imagem da biblioteca, inclusive as
        // que entraram antes da conversao para WebP.
        in_array(self::QUALQUER_IMAGEM, $mimes, strict: true)
            ? $consulta->where('mime_type', 'like', 'image/%')
            : $consulta->whereIn('mime_type', $mimes);

        return $consulta->value('id');
    }
}
