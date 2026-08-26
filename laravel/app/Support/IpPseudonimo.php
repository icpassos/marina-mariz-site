<?php

namespace App\Support;

/**
 * O IP em claro nunca e gravado (doc 08 e Politica de Privacidade, 2.2).
 * Guardamos so um HMAC com a IP_HASH_KEY, que continua sendo dado pessoal
 * pseudonimizado — nao dado anonimo.
 */
class IpPseudonimo
{
    public static function de(?string $ip): ?string
    {
        $chave = config('pessoas.ip_hash_key');

        // Sem chave nao ha pseudonimizacao possivel; entao nao se grava nada.
        if (blank($ip) || blank($chave)) {
            return null;
        }

        return hash_hmac('sha256', $ip, $chave);
    }
}
