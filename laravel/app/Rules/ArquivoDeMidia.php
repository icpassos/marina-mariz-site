<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Confere o arquivo pelo conteudo, nao pelo que o navegador declarou.
 * Regras do doc 05.
 */
class ArquivoDeMidia implements ValidationRule
{
    public const LARGURA_MAXIMA = 1920;

    public const PIXELS_MAXIMOS = 30_000_000;

    public const TAMANHO_MAXIMO = 50 * 1024 * 1024;

    /**
     * Extensao declarada => tipos que o conteudo pode ter de verdade.
     * As duas coisas precisam bater: so olhar o conteudo deixaria um .txt
     * entrar como .pdf, e so olhar a extensao nao verifica nada.
     */
    public const TIPOS_DE_ARQUIVO = [
        'pdf' => ['application/pdf'],
        'epub' => ['application/epub+zip'],
        'csv' => ['text/csv', 'text/plain'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
        'mp4' => ['video/mp4'],
        'webm' => ['video/webm'],
        'mp3' => ['audio/mpeg'],
        'm4a' => ['audio/mp4', 'audio/x-m4a', 'video/mp4'],
        'wav' => ['audio/wav', 'audio/x-wav'],
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach (is_array($value) ? $value : [$value] as $arquivo) {
            if (! $arquivo instanceof UploadedFile) {
                continue;
            }

            $this->validarArquivo($arquivo, $fail);
        }
    }

    private function validarArquivo(UploadedFile $arquivo, Closure $fail): void
    {
        $caminho = $arquivo->getRealPath();

        if ($caminho === false || ! is_file($caminho)) {
            $fail('Não foi possível ler o arquivo enviado.');

            return;
        }

        if (filesize($caminho) > self::TAMANHO_MAXIMO) {
            $fail('O arquivo passa de 50MB.');

            return;
        }

        // finfo le a assinatura do conteudo; o MIME do navegador e so um palpite.
        $tipo = (new \finfo(FILEINFO_MIME_TYPE))->file($caminho) ?: '';

        if (str_starts_with($tipo, 'image/')) {
            $this->validarImagem($caminho, $tipo, $fail);

            return;
        }

        $extensao = strtolower($arquivo->getClientOriginalExtension());
        $esperados = self::TIPOS_DE_ARQUIVO[$extensao] ?? null;

        if ($esperados === null) {
            $fail('Tipo de arquivo não aceito.');

            return;
        }

        if (! in_array($tipo, $esperados, strict: true)) {
            $fail("O conteúdo do arquivo não corresponde a um .{$extensao}.");
        }
    }

    private function validarImagem(string $caminho, string $tipo, Closure $fail): void
    {
        if ($tipo !== 'image/webp') {
            $fail('A imagem precisa chegar convertida em WebP. Envie JPEG, PNG ou WebP por um navegador atualizado — a conversão acontece nele.');

            return;
        }

        $dimensoes = @getimagesize($caminho);

        if ($dimensoes === false) {
            $fail('A imagem está corrompida ou não pôde ser lida.');

            return;
        }

        [$largura, $altura] = $dimensoes;

        if ($largura > self::LARGURA_MAXIMA) {
            $fail("A imagem tem {$largura}px de largura; o máximo é ".self::LARGURA_MAXIMA.'px.');

            return;
        }

        if ($largura * $altura > self::PIXELS_MAXIMOS) {
            $fail('A imagem passa de 30 milhões de pixels.');
        }
    }
}
