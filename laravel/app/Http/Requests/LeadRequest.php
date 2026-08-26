<?php

namespace App\Http\Requests;

class LeadRequest extends FormularioRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge($this->regrasComuns(), [
            'telefone' => ['nullable', 'string', 'max:30'],

            // O material vem por id da Biblioteca de Midia; o nome da origem
            // e lido do banco, nunca aceito do navegador.
            'media_id' => ['required', 'integer', 'exists:media,id'],

            // Consentimento de newsletter: opcional, especifico e desmarcado.
            'aceita_newsletter' => ['boolean'],
        ]);
    }
}
