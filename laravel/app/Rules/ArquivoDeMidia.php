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
     * Imagem entra em qualquer formato que o servidor consiga abrir — JPEG,
     * PNG, WebP, GIF, BMP, AVIF. O seletor do navegador usa este curinga e a
     * conferencia real e feita abrindo o arquivo, nao lendo o rotulo dele.
     */
    public const IMAGENS = ['image/*'];

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

    /** @param  array<int, string>  $extensoes  restringe o campo; vazio aceita imagem e qualquer tipo da lista */
    public function __construct(private array $extensoes = []) {}

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
            // Campo de arquivo não é campo de imagem, e vice-versa.
            $this->extensoes === []
                ? $this->validarImagem($caminho, $tipo, $fail)
                : $fail('Este campo espera um arquivo '.$this->lista().', não uma imagem.');

            return;
        }

        $extensao = strtolower($arquivo->getClientOriginalExtension());
        $esperados = self::TIPOS_DE_ARQUIVO[$extensao] ?? null;

        $aceita = $esperados !== null
            && ($this->extensoes === [] || in_array($extensao, $this->extensoes, strict: true));

        if (! $aceita) {
            $fail($this->extensoes === []
                ? 'Tipo de arquivo não aceito.'
                : 'Escolha um arquivo '.$this->lista().'.');

            return;
        }

        if (! in_array($tipo, $esperados, strict: true)) {
            $fail("O conteúdo do arquivo não corresponde a um .{$extensao}.");
        }
    }

    private function lista(): string
    {
        return '.'.implode(', .', $this->extensoes);
    }

    private function validarImagem(string $caminho, string $tipo, Closure $fail): void
    {
        // Qualquer imagem que o servidor consiga abrir serve: quem converte
        // para WebP e reduz para 1920px e `Media::guardar()`. Depender do
        // navegador para converter recusava foto de celular sem motivo.
        $dimensoes = @getimagesize($caminho);

        if ($dimensoes === false) {
            $fail('Não foi possível abrir esta imagem. Formatos aceitos: JPEG, PNG, WebP, GIF, BMP e AVIF'
                .' — HEIC do iPhone não abre aqui; exporte como JPEG antes de enviar.');

            return;
        }

        [$largura, $altura] = $dimensoes;

        // Largura grande e resolvida na conversao; area gigante nao, porque
        // e ela que estoura a memoria do GD antes de qualquer redimensionar.
        if ($largura * $altura > self::PIXELS_MAXIMOS) {
            $fail('A imagem passa de 30 milhões de pixels.');
        }
    }
}
