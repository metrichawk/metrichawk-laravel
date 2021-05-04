<?php

namespace Metrichawk\MetrichawkLaravel\Http\Middleware;

use Closure;
use Exception;
use Metrichawk\MetrichawkLaravel\Jobs\ExportJob;
use Metrichawk\MetrichawkLaravel\MetrichawkLaravel;
use Metrichawk\MetrichawkLaravel\Services\CollectorService;

class MonitorMiddleware
{
    /**
     * @param         $request
     * @param Closure $next
     * @param null    $guard
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
     *
     * @throws Exception
     */
    public function terminate($request, $response)
    {
        $requestDsn = config('metrichawk.dsn');
        $jobConfig  = config('metrichawk.job');

        $data = [
            'records' => [
                'common'   => $GLOBALS[MetrichawkLaravel::MH_COMMON],
                'requests' => $GLOBALS[MetrichawkLaravel::MH_REQUESTS],
                'queries'  => $GLOBALS[MetrichawkLaravel::MH_QUERIES] ?? null,
                'system'   => $GLOBALS[MetrichawkLaravel::MH_SYSTEM],
            ],
        ];

        if (isset($jobConfig['is_active']) === true && $jobConfig['is_active'] === true) {
            $job = new ExportJob($requestDsn, $data);

            if (isset($jobConfig['queue_name']) === true) {
                $job->onQueue($jobConfig['queue_name']);
            }

            dispatch($job);

            return;
        }

        if ($data['records']['queries'] !== null) {
            $data['records']['queries'] = resolve(CollectorService::class)->formatQueryData($data['records']['queries']);
        }

        retry(5, function () use ($requestDsn, $data) {
            resolve(CollectorService::class)->sendData($requestDsn, $data);
        }, 100);
    }
}
