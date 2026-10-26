<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    |
    | Este proyecto guarda las páginas de Inertia en resources/pages (no en
    | resources/js/pages), así que indicamos esa ruta al resolver componentes.
    |
    */

    'pages' => [
        'ensure_pages_exist' => false,

        'paths' => [
            resource_path('pages'),
        ],

        'extensions' => [
            'vue',
        ],
    ],
];
