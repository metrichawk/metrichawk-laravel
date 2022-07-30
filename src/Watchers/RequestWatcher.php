<?php

namespace Metrichawk\MetrichawkLaravel\Watchers;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Metrichawk\MetrichawkLaravel\MetrichawkLaravel;

class RequestWatcher extends Watcher
{
    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function register($app)
    {
        $app['events']->listen(RequestHandled::class, [$this, 'recordRequest']);
    }

    /**
     * @param RequestHandled $event
     */
    public function recordRequest(RequestHandled $event)
    {
        $endTime = microtime(true);

        $startTime = defined('LARAVEL_START') ? LARAVEL_START : $event->request->server('REQUEST_TIME_FLOAT');

        $data = [
            'duration' => round(($endTime - $startTime) * 1000, 2),
        ];

        MetrichawkLaravel::recordRequest($data);
    }
}
