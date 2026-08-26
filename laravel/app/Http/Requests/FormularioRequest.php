<?php

namespace App\Http\Requests;

use App\Support\IpPseudonimo;
use Illuminate\Foundation\Http\FormRequest;

/**
 * O que todo formulario do site tem em comum (docs 04 e 08).
 *
 * Validacao e sempre no servidor. A chave de idempotencia vem do navegador
 * e e conferida aqui antes de qualquer gravacao.
 */
abstract class FormularioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    protected function regrasComuns(): array
    {
        return [
            'submission_id' => ['required', 'uuid'],
            'nome' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:180'],

            // Ciencia da Politica, nao consentimento (doc 04).
            'ciencia_politica' => ['accepted'],

            // Honeypot: invisivel na tela, robo preenche, humano nao.
            'website' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'ciencia_politica.accepted' => 'É preciso confirmar que leu a Política de Privacidade.',
            'website.prohibited' => 'Não foi possível enviar o formulário.',
        ];
    }

    public function submissionId(): string
    {
        return $this->string('submission_id')->toString();
    }

    public function politicaVersao(): string
    {
        return config('pessoas.politica_versao');
    }

    /** IP pseudonimizado; o IP em claro nao chega ao banco. */
    public function ipHash(): ?string
    {
        return IpPseudonimo::de($this->ip());
    }
}
