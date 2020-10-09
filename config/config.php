<?php

use Metrichawk\MetrichawkLaravel\Watchers;

/*
 * You can place your custom package configuration in here.
 */
return [
    'enabled' => true,
    'dsn' => env('METRICHAWK_DSN'),
    'watchers' => [
        Watchers\RequestWatcher::class => [
            'enabled' => env('METRICHAWK_REQUEST_WATCHER', true),
            'size_limit' => env('METRICHAWK_RESPONSE_SIZE_LIMIT', 64),
        ]
    ]
];
