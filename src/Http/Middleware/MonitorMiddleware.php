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
     * @param Request $request
     * @param         $response
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function terminate(Request $request, Response $response)
    {
        $endTime = microtime(true);
        $startTime = constant('LARAVEL_START') ?? null;

        $durationInMs = round(($endTime - $startTime) * 1000, 2);

        $micro = sprintf("%06d", ($startTime - floor($startTime)) * 1000000);
        $startsAt = Carbon::parse(new DateTime(date('Y-m-d H:i:s.' . $micro, $startTime)));

        $micro = sprintf("%06d", ($endTime - floor($endTime)) * 1000000);
        $endsAt = Carbon::parse(new DateTime(date('Y-m-d H:i:s.' . $micro, $endTime)));


        $data = [
            'environment' => app()->environment(),
            'starts_at' => $startsAt->timestamp,
            'ends_at' => $endsAt->timestamp,
            'full_url' => $request->fullUrl(),
            'method' => $request->method(),
            'path' => $request->path(),
            'client_ip' => $request->getClientIp(),
            'host' => $request->getHost(),
            'locale' => $request->getLocale(),

            'duration' => $durationInMs,

            'route_name' => Route::current()->getName(),
            'response_status' => $response->getStatusCode(),
            'controller_action' => optional($request->route())->getActionName(),
            'middleware' => array_values(optional($request->route())->gatherMiddleware() ?? []),
            'headers' => $this->headers($request->headers->all()),
            'memory' => round(memory_get_peak_usage(true) / 1024 / 1025, 1),
        ];


        $dimensions = [
            ['Name' => 'environment', 'Value' => app()->environment()],
            ['Name' => 'start_time', 'Value' => strval($startsAt->timestamp)],
            ['Name' => 'end_time', 'Value' => strval($endsAt->timestamp)],
            ['Name' => 'full_url', 'Value' => $request->fullUrl()],
            ['Name' => 'method', 'Value' => $request->method()],
            ['Name' => 'path', 'Value' => $request->path()],
            ['Name' => 'client_ip', 'Value' => $request->getClientIp()],
            ['Name' => 'host', 'Value' => $request->getHost()],
            ['Name' => 'locale', 'Value' => $request->getLocale()],
        ];

        $records = [
            'Dimensions' => $dimensions,
            'MeasureName' => 'request_duration',
            'MeasureValue' => strval($durationInMs),
            'MeasureValueType' => 'DOUBLE',
            'Time' => strval(intval($startTime * 1000)),
        ];

        $requestDsn = config('metrichawk.dsn') . '/r/d';

        $client = new Client(['verify' => false]);
        $client->post($requestDsn, [
            'json' => [
                'records' => $records
            ]
        ]);
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
