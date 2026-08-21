<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    /** @use HasFactory<\Database\Factories\MediaFactory> */
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'uuid',
        'path',
        'original_name',
        'name',
        'mime_type',
        'size',
        'width',
        'height',
        'alt',
        'is_decorative',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'is_decorative' => 'boolean',
        ];
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * URL assinada e temporaria do disco privado. Nao existe caminho
     * publico para estes arquivos.
     */
    public function url(): string
    {
        return Storage::disk('local')->temporaryUrl($this->path, now()->addMinutes(30));
    }

    /**
     * Le mime, peso e dimensoes do arquivo ja gravado no disco privado.
     * Nada aqui vem do navegador.
     *
     * @return array{mime_type: string, size: int, width: ?int, height: ?int}
     */
    public static function metadadosDe(string $path): array
    {
        $disco = Storage::disk('local');
        $absoluto = $disco->path($path);

        $dados = [
            'mime_type' => (new \finfo(FILEINFO_MIME_TYPE))->file($absoluto) ?: 'application/octet-stream',
            'size' => $disco->size($path),
            'width' => null,
            'height' => null,
        ];

        if (str_starts_with($dados['mime_type'], 'image/')) {
            $dimensoes = @getimagesize($absoluto);

            if ($dimensoes !== false) {
                [$dados['width'], $dados['height']] = $dimensoes;
            }
        }

        return $dados;
    }

    /**
     * ponytail: ainda nao ha nada consumindo midia. Quando Blog e Educacao
     * existirem, a chave estrangeira com restrictOnDelete e quem bloqueia
     * a exclusao de arquivo em uso, e este metodo passa a listar onde.
     */
    public function usos(): array
    {
        return [];
    }

    protected static function booted(): void
    {
        static::deleted(function (Media $media): void {
            Storage::disk('local')->delete($media->path);
        });
    }
}
