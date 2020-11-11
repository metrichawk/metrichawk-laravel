<?php

namespace Metrichawk\MetrichawkLaravel\Watchers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Jenssegers\Agent\Agent;
use Metrichawk\MetrichawkLaravel\Helpers\IpAnonymizer;
use Metrichawk\MetrichawkLaravel\Http\Middleware\MonitorMiddleware;
use Metrichawk\MetrichawkLaravel\MetrichawkLaravel;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Contracts\EventDispatcher\Event;
use DateTime;

class RequestWatcher extends Watcher
{
    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register($app)
    {
        //$app->singleton(MonitorMiddleware::class);
        //$dispatcher = new EventDispatcher();
        //$dispatcher->addListener(KernelEvents::TERMINATE, function (Event $event){
        //    dd($event);
        //    $this->recordRequest($event);
        //});
        //$app['events']->listen(RequestHandled::class, [$this, 'recordRequest2']);

        $app['events']->listen(RequestHandled::class, [$this, 'recordRequest']);
    }

    /**
     * @param RequestHandled $event
     */
    public function recordRequest(RequestHandled $event)
    {
        $request = $event->request;

        $endTime = microtime(true);
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : $request->server('REQUEST_TIME_FLOAT');

        $data = [
            'duration' => round(($endTime - $startTime) * 1000, 2),
        ];

        MetrichawkLaravel::recordRequest($data);
    }
}
