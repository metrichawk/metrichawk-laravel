<?php

namespace Metrichawk\MetrichawkLaravel;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Jenssegers\Agent\Agent;
use Metrichawk\MetrichawkLaravel\Helpers\IpAnonymizer;
use Metrichawk\MetrichawkLaravel\Http\Middleware\MonitorMiddleware;
use Metrichawk\MetrichawkLaravel\Traits\RegistersWatchers;

class MetrichawkLaravel
{
    use RegistersWatchers;

    const MH_QUERIES = 'mh_queries';
    const MH_REQUESTS = 'mh_requests';
    const MH_SYSTEM = 'mh_system';

    public static function start($app)
    {
        if (!config('metrichawk.enabled')) {
            return;
        }

        $kernel = $app->make(Kernel::class);
        $kernel->pushMiddleware(MonitorMiddleware::class);

        static::registerWatchers($app);
    }

    /**
     * @param array $values
     */
    public static function recordQuery(array $values)
    {
        self::recordDataAsArray(self::MH_QUERIES, $values);
    }

    /**
     * @param array $values
     */
    public static function recordRequest(array $values)
    {
        self::recordData(self::MH_REQUESTS, $values);
    }

    /**
     * @param array $values
     */
    public static function recordSystem(array $values)
    {
        self::recordData(self::MH_SYSTEM, $values);
    }

    /**
     * @param string $key
     * @param array $values
     */
    private static function recordData(string $key, array $values)
    {
        if (isset($GLOBALS[$key]) === false) {
            $GLOBALS[$key] = [];
        }

        $GLOBALS[$key] = $values;
    }

    /**
     * @param string $key
     * @param array $values
     */
    private static function recordDataAsArray(string $key, array $values)
    {
        if (isset($GLOBALS[$key]) === false) {
            $GLOBALS[$key] = [];
        }

        $GLOBALS[$key][] = $values;
    }
}
