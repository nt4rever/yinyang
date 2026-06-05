<?php

return [
    /*
     * This is the default namespace that will be
     * used by all metrics
     */
    'default_namespace' => 'app',

    /*
     * This is the list of routes that will be
     * ignored by the metrics collector
     */
    'ignored_routes' => [
        'livewire*',
        'nova-api*',
        'pulse*',
        'horizon*',
        'telescope*',
        '.well-known*',
        'metrics',
        'health',
    ],

    /**
     * Select a cache to store gauges, counters, summaries and histograms between requests.
     * In a multi node setup you should ensure that each node writes to its own
     * cache instance or uses a node specific prefix.
     * Configure the cache store in config/cache.php.
     *
     * to use an in memory adapter for testing use array or null as your store
     * or remove the cache entry all together:
     *  'cache' => null       // InMemory implementation without laravel cache
     *  'cache' => 'array'    // InMemory implementation using laravel cache
     */
    'cache' => config('cache.default'),

    /*
     * Bearer token for the /metrics endpoint.
     * Set to null to disable authentication.
     */
    'metrics_token' => env('PROMETHEUS_METRICS_TOKEN', null),
];
