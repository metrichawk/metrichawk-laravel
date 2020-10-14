<?php

namespace Metrichawk\MetrichawkLaravel\Watchers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Contracts\Http\Kernel;
use Metrichawk\MetrichawkLaravel\Http\Middleware\MonitorMiddleware;
use Symfony\Contracts\EventDispatcher\Event;
use DateTime;

class RequestWatcher extends Watcher
{
    /**
     * Register the watcher.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @return void
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
        //$app['events']->listen(TerminateEvent::class, [$this, 'recordRequest']);

        $kernel = $app->make(Kernel::class);
        $kernel->pushMiddleware(MonitorMiddleware::class);
    }
}
