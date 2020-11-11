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

class CommonWatcher extends Watcher
{
    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register($app)
    {
        $app['events']->listen(RequestHandled::class, [$this, 'recordCommon']);
    }

    /**
     * @param RequestHandled $event
     */
    public function recordCommon(RequestHandled $event)
    {
        $request = $event->request;
        $response = $event->response;

        $startTime = defined('LARAVEL_START') ? LARAVEL_START : $request->server('REQUEST_TIME_FLOAT');

        /** @var Agent $agent */
        $agent = resolve(Agent::class);

        $browser = $agent->browser();
        $platform = $agent->platform();
        $platformVersion = $agent->version($platform);

        $data = [
            'environment' => app()->environment(),
            'full_url' => $request->fullUrl(),
            'method' => $request->method(),
            'path' => $request->path(),
            'client_ip' => IpAnonymizer::anonymizeIp(request()->server('HTTP_CF_CONNECTING_IP') ?? $request->ip()),
            'host' => $request->getHost(),
            'referer' => $_SERVER['HTTP_REFERER'] ?? null,
            'locale' => $request->getLocale(),
            'route_name' => optional(Route::current())->getName(),
            'response_status' => $response->getStatusCode(),
            'controller_action' => optional($request->route())->getActionName() ?? null,
            'starts_at' => $startTime,

            'browser' => $browser,
            'browser_version' => $agent->version($browser),
            'device' => $agent->device(),
            'country' => $request->server('HTTP_CF_IPCOUNTRY') ?? null,
            'device_type' => $agent->deviceType(),
            'platform' => $platform,
            'platform_version' => $platformVersion === false ? null : $platformVersion,
        ];

        MetrichawkLaravel::recordCommon($data);
    }
}
