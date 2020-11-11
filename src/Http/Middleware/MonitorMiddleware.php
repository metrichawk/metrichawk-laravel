<?php

namespace Metrichawk\MetrichawkLaravel\Http\Middleware;

use Closure;
use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Collection;
use Metrichawk\MetrichawkLaravel\MetrichawkLaravel;

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
        $requestDsn = config('metrichawk.dsn');

        $client = new Client([
            'verify' => false,
            'timeout' => 1
        ]);

        try {
            // TODO : maybe attached to the response and send data with js client to avoid supplementary time
            $client->post($requestDsn, [
                'json' => [
                    'records' => [
                        'common' => $GLOBALS[MetrichawkLaravel::MH_COMMON],
                        'requests' => $GLOBALS[MetrichawkLaravel::MH_REQUESTS],
                        'queries' => $this->formatQueryData(),
                        'system' => $GLOBALS[MetrichawkLaravel::MH_SYSTEM],
                    ]
                ]
            ]);
        } catch (Exception $exception) {
            // @TODO : something goes wrong
        }
    }

    /**
     * @return array
     */
    public function formatQueryData(): array
    {
        $data = [];

        $queriesByConnection = collect($GLOBALS[MetrichawkLaravel::MH_QUERIES])->groupBy('connection_name');

        $queriesByConnection->each(function(Collection $queries, string $connectionName) use (&$data) {
            $sqlDuration = $queries->sum('duration');

            $sqlDuplicationCount = $queries->countBy('hash')->sum(function ($count) {
                if ($count > 1) {
                    return $count;
                }

                return 0;
            });

            $data[] = [
                'connection_name' => $connectionName,
                'duration' => $sqlDuration,
                'duplication_count' => $sqlDuplicationCount,
            ];
        });

        return $data;
    }
}
