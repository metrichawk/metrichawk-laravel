<?php

use Metrichawk\MetrichawkLaravel\Watchers;

/*
 * You can place your custom package configuration in here.
 */
return [
    'enabled' => env('METRICHAWK_ENABLED', true),
    'dsn' => env('METRICHAWK_DSN'),
    'watchers' => [
        Watchers\QueryWatcher::class => [
            'enabled' => env('METRICHAWK_QUERY_WATCHER', true),
        ],
        Watchers\SystemWatcher::class => [
            'enabled' => env('METRICHAWK_SYSTEM_WATCHER', true),
        ]
    ]
];
