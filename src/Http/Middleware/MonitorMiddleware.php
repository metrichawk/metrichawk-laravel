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
use Exception;
use Metrichawk\MetrichawkLaravel\Helpers\IpAnonymizer;

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
     * TODO
     * On Vapor, terminate is not called after the response
     * Maybe : add a Job ? attach data to the view and send with JS ?
     *
     * @param $request
     * @param $response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function terminate($request, $response)
    {
        $endTime = microtime(true);
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : $request->server('REQUEST_TIME_FLOAT');

        /** @var Agent $agent */
        $agent = resolve(Agent::class);

        $browser = $agent->browser();
        $platform = $agent->platform();

        dump($_SERVER['HTTP_REFERER'] ?? '-');

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

        $requestDsn = config('metrichawk.dsn') . '/r/d';

        $client = new Client([
            'verify' => false,
            'timeout' => 1
        ]);

        try {
            $client->post($requestDsn, [
                'json' => [
                    'records' => $data
                ]
            ]);
        } catch (Exception $exception) {
            // @TODO : something goes wrong
        }
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
