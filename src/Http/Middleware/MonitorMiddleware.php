<?php

namespace Metrichawk\MetrichawkLaravel\Http\Middleware;

use Carbon\Carbon;
use Closure;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Jenssegers\Agent\Agent;

class MonitorMiddleware
{
    /**
     * @param         $request
     * @param Closure $next
     * @param null $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        return $next($request);
    }

    /**
     * @param $request
     * @param $response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function terminate($request, $response)
    {
        $endTime = microtime(true);
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : $request->server('REQUEST_TIME_FLOAT');

        $durationInMs = round(($endTime - $startTime) * 1000, 2);

        $micro = sprintf("%06d", ($startTime - floor($startTime)) * 1000000);
        $startsAt = Carbon::parse(new DateTime(date('Y-m-d H:i:s.' . $micro, $startTime)));

        $micro = sprintf("%06d", ($endTime - floor($endTime)) * 1000000);
        $endsAt = Carbon::parse(new DateTime(date('Y-m-d H:i:s.' . $micro, $endTime)));


        /** @var Agent $agent */
        $agent = resolve(Agent::class);

        $data = [
            'duration' => $durationInMs,

            'environment' => app()->environment(),
            'starts_at' => $startsAt->timestamp,
            'ends_at' => $endsAt->timestamp,
            'full_url' => $request->fullUrl(),
            'method' => $request->method(),
            'path' => $request->path(),
            'client_ip' => request()->server('HTTP_CF_CONNECTING_IP') ?? $request->ip(),
            'host' => $request->getHost(),
            'locale' => $request->getLocale(),

            'browser'          => $agent->browser(),
            'browser_version'  => $agent->version($agent->browser()),
            'device'           => $agent->device(),
            'country'          => $request->server('HTTP_CF_IPCOUNTRY') ?? null,
            'device_type'      => self::getDeviceType($agent),
            'platform'         => $agent->platform(),
            'platform_version' => $agent->version($agent->platform()),

            'route_name' => optional(Route::current())->getName(),
            'response_status' => $response->getStatusCode(),
            'controller_action' => optional($request->route())->getActionName(),
            //'middleware' => implode(',', array_values(optional($request->route())->gatherMiddleware() ?? [])),
            'memory' => round(memory_get_peak_usage(true) / 1024 / 1025, 1),
        ];

        $requestDsn = config('metrichawk.dsn') . '/r/d';

        $client = new Client(['verify' => false]);
        $client->post($requestDsn, [
            'json' => [
                'records' => $data
            ]
        ]);
    }

    /**
     * @param Agent $agent
     *
     * @return string
     */
    protected function getDeviceType(Agent $agent): string
    {
        if ($agent->isMobile()) {
            return 'mobile';
        } elseif ($agent->isTablet()) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * @param $headers
     * @return array
     */
    protected function headers($headers): array
    {
        return collect($headers)->map(function ($header) {
            return $header[0];
        })->toArray();
    }
}
