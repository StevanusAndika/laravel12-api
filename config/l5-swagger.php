<?php

return [
    'api' => [
        'title' => 'dokumentasi keperluan internal',
    ],
    'routes' => [
        'api' => 'api/documentation',
        'docs' => 'docs',
    ],
    'paths' => [
        'docs' => storage_path('api-docs'),
        'annotations' => base_path('app'),
    ],
];
