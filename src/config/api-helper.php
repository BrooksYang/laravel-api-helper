<?php

return [

    // Cache tag prefix
    'cache_tag_prefix' => env('CACHE_TAG_PREFIX', 'api_helper'),

    // Cache ttl
    'cache_ttl'        => env('CACHE_TTL', 120),

    // Api Base Url
    'api_base_url'     => env('API_BASE_URL'),

    // Enable Api Doc
    'api_doc' => env('API_DOC', true),

    // Enable Pressure Test
    'api_pressure_test' => env('API_PRESSURE_TEST', false),

    // Available Namespaces（key: group, value: namespace）
    'namespaces'       => [
        'Helper' => 'BrooksYang\LaravelApiHelper\Controllers\BuiltIn',
        'App' => 'App\Http\Controllers',
    ],
];
