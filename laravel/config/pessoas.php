<?php

/**
 * Modulo Pessoas (docs 04 e 08). Arquivo proprio para nada aqui depender
 * de env() em tempo de execucao: com `artisan optimize` em producao, env()
 * fora de config devolve null.
 */
return [

    // Versao do aviso de privacidade gravada junto de cada ciencia e de
    // cada consentimento. Vem do servidor; o formulario nao a envia.
    'politica_versao' => env('POLITICA_VERSAO', '2026-08-21'),

    // Chave do HMAC que pseudonimiza o IP. Sem ela nao gravamos IP algum.
    'ip_hash_key' => env('IP_HASH_KEY'),

    // Caixa que recebe o aviso de cada registro novo.
    'email_equipe' => env('MAIL_TO', 'contato@dramarinamariz.com.br'),

    // Pasta do backup semanal dos contatos: fora do public_html e fora do
    // laravel/. Se nao existir ou nao for gravavel, o comando cai no
    // storage/ e registra no log.
    'backup_contatos_path' => env('BACKUP_CONTATOS_PATH', '/home/u442546272/backups-contatos'),

    'outbox' => [
        // Quantos itens o comando tenta por execucao do cron (a cada 5 min).
        'lote' => 50,
        // Espera antes de cada nova tentativa, em minutos. Depois da ultima,
        // o item vira "falhou" e para de tentar sozinho.
        'backoff' => [1, 5, 15, 60, 180],
    ],
];
