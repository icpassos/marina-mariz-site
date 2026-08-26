<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

/**
 * Aviso de Cookies. A escolha mora num cookie proprio, e nao em
 * localStorage, porque o servidor precisa le-la ANTES de montar o HTML:
 * injetar o script de rastreamento e "desligar" por JavaScript depois nao
 * vale nada — o pedido ja saiu do navegador e o dado ja foi enviado.
 */
class AvisoDeCookies
{
    public const COOKIE = 'consentimento';

    /**
     * Versao do aviso. Subir esta linha faz o aviso reaparecer para todo
     * mundo pedir consentimento de novo — e o que se faz quando entra uma
     * ferramenta nova de rastreamento.
     */
    public const VERSAO = '2026-08-21';

    /** Validade de 12 meses, em minutos. */
    public const VALIDADE = 60 * 24 * 365;

    /**
     * A escolha gravada no navegador. Devolve null quando nao ha escolha
     * ou quando ela e de uma versao antiga do aviso — nos dois casos o
     * aviso aparece e nada de rastreamento entra na pagina.
     *
     * @return array{analiticos: bool, marketing: bool}|null
     */
    public static function escolha(Request $request): ?array
    {
        $valor = json_decode((string) $request->cookie(self::COOKIE), true);

        if (! is_array($valor) || ($valor['versao'] ?? null) !== self::VERSAO) {
            return null;
        }

        return [
            'analiticos' => (bool) ($valor['analiticos'] ?? false),
            'marketing' => (bool) ($valor['marketing'] ?? false),
        ];
    }

    /** Grava a escolha: cookie no navegador e registro no banco. */
    public static function registrar(Request $request, bool $analiticos, bool $marketing): void
    {
        $agora = now();

        Cookie::queue(Cookie::make(
            name: self::COOKIE,
            value: json_encode([
                'versao' => self::VERSAO,
                'analiticos' => $analiticos,
                'marketing' => $marketing,
                'em' => $agora->toIso8601String(),
            ]),
            minutes: self::VALIDADE,
            path: '/',
            domain: null,
            secure: $request->isSecure(),
            // Sem HttpOnly de proposito: a interface de preferencias
            // precisa ler. O valor so controla script opcional; nunca
            // concede autorizacao administrativa.
            httpOnly: false,
            raw: false,
            sameSite: 'lax',
        ));

        // Prova de consentimento exigida pelo doc 00: versao do aviso,
        // data, hora e IP pseudonimizado. Nunca o IP em claro.
        DB::table('consentimentos_de_cookies')->insert([
            'versao_aviso' => self::VERSAO,
            'analiticos' => $analiticos,
            'marketing' => $marketing,
            'ip_hash' => IpPseudonimo::de($request->ip()),
            'decidido_em' => $agora,
            'created_at' => $agora,
            'updated_at' => $agora,
        ]);
    }
}
