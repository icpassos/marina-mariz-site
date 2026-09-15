<?php

namespace App\Http\Requests;

use App\Enums\OrigemMensagem;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class MensagemRequest extends FormularioRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $extrasObrigatorios = $this->origem() === OrigemMensagem::FormacaoProfissional;

        return array_merge($this->regrasComuns(), [
            'iniciado_em' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                try {
                    $iniciadoEm = (int) Crypt::decryptString($value);
                } catch (DecryptException) {
                    $fail('Não foi possível enviar o formulário.');

                    return;
                }

                if ($iniciadoEm > now()->subSeconds(3)->timestamp) {
                    $fail('Não foi possível enviar o formulário.');
                }
            }],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'texto' => ['required', 'string', 'max:5000'],

            // So a Formacao Profissional pede estes campos (doc 04).
            'profissao' => [$extrasObrigatorios ? 'required' : 'prohibited', 'string', 'max:120'],
            // CRM/COREN e opcional no formulario (escopo: Formacao Profissional):
            // doula e acompanhante nao tem registro para informar.
            'registro_profissional' => [$extrasObrigatorios ? 'nullable' : 'prohibited', 'string', 'max:60'],
            'instituicao' => [$extrasObrigatorios ? 'nullable' : 'prohibited', 'string', 'max:180'],
            'modalidade' => [$extrasObrigatorios ? 'nullable' : 'prohibited', 'string', 'max:120'],
        ]);
    }

    /** A origem vem da rota, nunca do corpo do formulario. */
    public function origem(): OrigemMensagem
    {
        return $this->routeIs('formularios.formacao')
            ? OrigemMensagem::FormacaoProfissional
            : OrigemMensagem::Contato;
    }
}
