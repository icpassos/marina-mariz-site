<?php

namespace App\Http\Requests;

class NewsletterRequest extends FormularioRequest
{
    /**
     * De onde a inscricao veio. E uma lista so: a origem serve para o
     * painel saber a procedencia. Toda pagina de Educacao termina com o
     * bloco de inscricao (escopos das paginas), por isso as seis estao
     * aqui; `newsletter` e a pagina de apoio /newsletter, linkada no rodape.
     */
    public const ORIGENS = [
        'blog', 'newsletter',
        'livros', 'ebooks', 'cursos', 'eventos', 'materiais', 'formacao-profissional',
    ];

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge($this->regrasComuns(), [
            'nome' => ['nullable', 'string', 'max:120'],
            'origem' => ['required', 'in:'.implode(',', self::ORIGENS)],

            // Aqui o consentimento e a propria finalidade do formulario, mas
            // continua sendo caixa propria e desmarcada na tela.
            'aceita_newsletter' => ['accepted'],
        ]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return parent::messages() + [
            'aceita_newsletter.accepted' => 'É preciso marcar que aceita receber a newsletter.',
        ];
    }
}
