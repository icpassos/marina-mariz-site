<?php

return [
    'sets' => [
        /*
         * Os mesmos icones do site: Phosphor regular, os arquivos que o
         * `@phosphor-icons/core` publica. Ficam no repositorio porque o
         * painel nao carrega CDN — o site usa a fonte, aqui e SVG em linha.
         */
        'phosphor' => [
            'path' => 'resources/svg/phosphor',
            'prefix' => 'ph',
        ],
    ],
];
