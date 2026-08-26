<?php

/**
 * Roteador de desenvolvimento.
 *
 * Faz o mesmo que o roteador do `artisan serve` — emula o mod_rewrite do
 * Apache —, mas entrega video e audio com requisicao parcial (206). O
 * servidor embutido do PHP nao implementa `Range` nem anuncia
 * `Accept-Ranges`, e sem isso o navegador nao reproduz midia: e por isso
 * que o video da home aparece parado, com botao de play, so no ambiente
 * local. Em producao, com Apache, isso ja funciona.
 *
 * Uso, dentro de `laravel/`:
 *   /opt/homebrew/opt/php@8.4/bin/php -S 127.0.0.1:8899 -t public server-dev.php
 */
$tipos = [
    'mp4' => 'video/mp4',
    'webm' => 'video/webm',
    'ogv' => 'video/ogg',
    'mov' => 'video/quicktime',
    'mp3' => 'audio/mpeg',
    'm4a' => 'audio/mp4',
    'wav' => 'audio/wav',
];

// Este roteador mora na raiz do projeto; o `getcwd()` do servidor embutido
// nao aponta para `public/`.
$publicPath = __DIR__.'/public';
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');
$arquivo = $publicPath.$uri;
$tipo = $tipos[strtolower(pathinfo($uri, PATHINFO_EXTENSION))] ?? null;

if ($tipo !== null && is_file($arquivo)) {
    $tamanho = filesize($arquivo);
    $inicio = 0;
    $fim = $tamanho - 1;

    // `bytes=inicio-fim`, com qualquer um dos dois lados opcional. So a
    // primeira faixa: navegador nenhum pede varias para midia.
    if (preg_match('/^bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'] ?? '', $pedido)) {
        [$inicio, $fim] = $pedido[1] === ''
            ? [max(0, $tamanho - (int) $pedido[2]), $tamanho - 1]
            : [(int) $pedido[1], $pedido[2] === '' ? $tamanho - 1 : min((int) $pedido[2], $tamanho - 1)];

        if ($inicio > $fim || $inicio >= $tamanho) {
            header('Accept-Ranges: bytes');
            header("Content-Range: bytes */$tamanho", true, 416);

            return true;
        }

        header("Content-Range: bytes $inicio-$fim/$tamanho", true, 206);
    }

    header("Content-Type: $tipo");
    header('Accept-Ranges: bytes');
    header('Content-Length: '.($fim - $inicio + 1));

    $puxador = fopen($arquivo, 'rb');
    fseek($puxador, $inicio);
    $restante = $fim - $inicio + 1;

    while ($restante > 0 && ! feof($puxador)) {
        $pedaco = fread($puxador, min(262144, $restante));
        echo $pedaco;
        $restante -= strlen($pedaco);
    }

    fclose($puxador);

    return true;
}

// Fora de midia, e o roteador de sempre: arquivo existente sai pelo proprio
// servidor embutido; o resto vai para o Laravel.
if ($uri !== '/' && file_exists($arquivo)) {
    return false;
}

require_once $publicPath.'/index.php';
