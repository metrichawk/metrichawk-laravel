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

class SystemWatcher extends Watcher
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

        $app['events']->listen(RequestHandled::class, [$this, 'recordSystem']);
    }

    /**
     * @param RequestHandled $event
     */
    public function recordSystem(RequestHandled $event)
    {
        $data = [
            'memory' => round(memory_get_peak_usage(true) / 1024 / 1025, 1),
            'cpu' => sys_getloadavg(),
        ];

        MetrichawkLaravel::recordSystem($data);
    }
}
