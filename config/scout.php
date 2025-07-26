<?php

use App\Models\Product;

return [

    'driver' => env('SCOUT_DRIVER', 'typesense'), // <-- modificat aici

    'prefix' => env('SCOUT_PREFIX', ''),

    'queue' => env('SCOUT_QUEUE', false),

    'after_commit' => false,

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    'soft_delete' => false,

    'identify' => env('SCOUT_IDENTIFY', false),

    'algolia' => [
        'id' => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
        'index-settings' => [],
    ],

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [],
    ],

    'typesense' => [
        'client-settings' => [
            'api_key' => env('TYPESENSE_API_KEY', 'xyz'),
            'nodes' => [
                [
                    'host' => env('TYPESENSE_HOST', 'localhost'),
                    'port' => env('TYPESENSE_PORT', '8108'),
                    'path' => env('TYPESENSE_PATH', ''),
                    'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
                ],
            ],
            'nearest_node' => [
                'host' => env('TYPESENSE_HOST', 'localhost'),
                'port' => env('TYPESENSE_PORT', '8108'),
                'path' => env('TYPESENSE_PATH', ''),
                'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
            ],
            'connection_timeout_seconds' => env('TYPESENSE_CONNECTION_TIMEOUT_SECONDS', 2),
            'healthcheck_interval_seconds' => env('TYPESENSE_HEALTHCHECK_INTERVAL_SECONDS', 30),
            'num_retries' => env('TYPESENSE_NUM_RETRIES', 3),
            'retry_interval_seconds' => env('TYPESENSE_RETRY_INTERVAL_SECONDS', 1),
        ],
        'model-settings' => [
            Product::class => [
                'collection-schema' => [
                    'fields' => [
                        ['name' => 'id', 'type' => 'string'],
                        ['name' => 'title', 'type' => 'string', 'sort' => true],
                        ['name' => 'description', 'type' => 'string'],
                        ['name' => 'department_name', 'type' => 'string', 'facet' => true],
                        ['name' => 'category_name', 'type' => 'string', 'facet' => true],
                        ['name' => 'price', 'type' => 'float', 'facet' => true],
                        ['name' => 'gross_price', 'type' => 'float'],
                        ['name' => 'vat_rate_type', 'type' => 'string'], // <-- inclus aici
                        ['name' => 'created_at', 'type' => 'int64'],
                    ],
                    'default_sorting_field' => 'created_at',
                ],
                'search-parameters' => [
                    'query_by' => 'title,description',
                ],
            ],
        ],
    ],

];
