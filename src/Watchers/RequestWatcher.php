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
        $endTime = microtime(true);
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : $request->server('REQUEST_TIME_FLOAT');

        $request = $event->request;
        $response = $event->response;

        /** @var Agent $agent */
        $agent = resolve(Agent::class);

        $browser = $agent->browser();
        $platform = $agent->platform();

        $data = [
            'environment' => app()->environment(),
            'starts_at' => $startTime,
            'ends_at' => $endTime,
            'full_url' => $request->fullUrl(),
            'method' => $request->method(),
            'path' => $request->path(),
            'client_ip' => IpAnonymizer::anonymizeIp(request()->server('HTTP_CF_CONNECTING_IP') ?? $request->ip()),
            'host' => $request->getHost(),
            'referer' => $_SERVER['HTTP_REFERER'] ?? '-',
            'locale' => $request->getLocale(),

            'browser' => $browser,
            'browser_version' => $agent->version($browser),
            'device' => $agent->device(),
            'country' => $request->server('HTTP_CF_IPCOUNTRY') ?? '-',
            'device_type' => $agent->deviceType(),
            'platform' => $platform,
            'platform_version' => $agent->version($platform),

            'route_name' => optional(Route::current())->getName(),
            'response_status' => $response->getStatusCode(),
            'controller_action' => optional($request->route())->getActionName() ?? '-',

            'memory' => round(memory_get_peak_usage(true) / 1024 / 1025, 1),
            'cpu' => sys_getloadavg(),
        ];

        MetrichawkLaravel::recordRequest($data);
    }
}
