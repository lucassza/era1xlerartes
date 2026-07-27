<?php

declare(strict_types=1);

return [
    'tdbmix' => [
        'base_url' => env('TDBMIX_BASE_URL', 'https://www.tdbmix.com.br'),
        'sitemap_url' => env('TDBMIX_SITEMAP_URL', 'https://www.tdbmix.com.br/sitemap.xml'),
        'default_author_name' => env('TDBMIX_DEFAULT_AUTHOR_NAME', 'TDBMix'),
        'default_category_name' => env('TDBMIX_DEFAULT_CATEGORY_NAME', 'Importados'),
        'request_timeout' => (int) env('TDBMIX_REQUEST_TIMEOUT', 20),
    ],
];
