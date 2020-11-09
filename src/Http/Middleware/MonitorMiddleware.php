<?php

namespace Metrichawk\MetrichawkLaravel\Http\Middleware;

use Closure;
use GuzzleHttp\Client;
use Exception;

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
            $client->post($requestDsn, [
                'json' => [
                    'records' => [
                        'requests' => $GLOBALS['mh_requests'],
                        'queries' => $GLOBALS['mh_queries'],
                    ]
                ]
            ]);
        } catch (Exception $exception) {
            // @TODO : something goes wrong
        }
    }
}
