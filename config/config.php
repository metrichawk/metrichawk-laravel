<?php

use Metrichawk\MetrichawkLaravel\Watchers;

/*
 * You can place your custom package configuration in here.
 */
return [
    'enabled'  => env('METRICHAWK_ENABLED', true),
    'dsn'      => env('METRICHAWK_DSN'),

    /**
     * WATCHERS
     * Determine if watchers are enabled or not
     */
    'watchers' => [
        Watchers\QueryWatcher::class  => [
            'enabled' => env('METRICHAWK_QUERY_WATCHER', true),
        ],
        Watchers\SystemWatcher::class => [
            'enabled' => env('METRICHAWK_SYSTEM_WATCHER', false),
        ],
    ],

    /**
     * JOBS
     * Job is recommended for Vapor because the data won't be send
     * after the response but during the request life cycle
     */
    'job'      => [
        'is_active'  => true,
        'queue_name' => env('METRICHAWK_QUEUE_NAME', 'default'),
    ],
];
