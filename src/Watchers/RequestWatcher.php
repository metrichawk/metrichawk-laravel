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

    /**
     * @param Event $event
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function recordRequest(Event $event)
    {
        $request = $event->getRequest();

        $end   = microtime(true);
        $start = constant('LARAVEL_START');

        $durationInMs = round(($end - $start) * 1000, 2);

        $micro = sprintf("%06d", ($start - floor($start)) * 1000000);
        $startDate  = Carbon::parse(new DateTime(date('Y-m-d H:i:s.' . $micro, $start)));

        $micro = sprintf("%06d", ($end - floor($end)) * 1000000);
        $endDate  = Carbon::parse(new DateTime(date('Y-m-d H:i:s.' . $micro, $end)));

        $dimensions = [
            ['Name' => 'environment', 'Value' => app()->environment()],
            ['Name' => 'start_time', 'Value' => strval($startDate->timestamp)],
            ['Name' => 'end_time', 'Value' => strval($endDate->timestamp)],
            ['Name' => 'full_url', 'Value' => $request->fullUrl()],
            ['Name' => 'method', 'Value' => $request->method()],
            ['Name' => 'path', 'Value' => $request->path()],
            ['Name' => 'client_ip', 'Value' => $request->getClientIp()],
            ['Name' => 'host', 'Value' => $request->getHost()],
            ['Name' => 'locale', 'Value' =>  $request->getLocale()],
        ];

        $records = [
            'Dimensions'       => $dimensions,
            'MeasureName'      => 'request_duration',
            'MeasureValue'     => strval($durationInMs),
            'MeasureValueType' => 'DOUBLE',
            'Time'             => strval(intval($start * 1000)),
        ];

        $requestDsn = config('metrichawk.dsn') . '/r/d';

        $client = new Client([ 'verify' => false ]);
        $client->post($requestDsn, [
            'json' => [
                'records' => $records
            ]
        ]);
    }

    /**
     * @param RequestHandled $event
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function recordRequest2(RequestHandled $event)
    {
        $request = $event->request;

        $end   = microtime(true);
        $start = constant('LARAVEL_START');

        $durationInMs = round(($end - $start) * 1000, 2);

        $micro = sprintf("%06d", ($start - floor($start)) * 1000000);
        $startDate  = Carbon::parse(new DateTime(date('Y-m-d H:i:s.' . $micro, $start)));

        $micro = sprintf("%06d", ($end - floor($end)) * 1000000);
        $endDate  = Carbon::parse(new DateTime(date('Y-m-d H:i:s.' . $micro, $end)));

        $dimensions = [
            ['Name' => 'environment', 'Value' => app()->environment()],
            ['Name' => 'start_time', 'Value' => strval($startDate->timestamp)],
            ['Name' => 'end_time', 'Value' => strval($endDate->timestamp)],
            ['Name' => 'full_url', 'Value' => $request->fullUrl()],
            ['Name' => 'method', 'Value' => $request->method()],
            ['Name' => 'path', 'Value' => $request->path()],
            ['Name' => 'client_ip', 'Value' => $request->getClientIp()],
            ['Name' => 'host', 'Value' => $request->getHost()],
            ['Name' => 'locale', 'Value' =>  $request->getLocale()],
        ];

        $records = [
            'Dimensions'       => $dimensions,
            'MeasureName'      => 'request_duration',
            'MeasureValue'     => strval($durationInMs),
            'MeasureValueType' => 'DOUBLE',
            'Time'             => strval(intval($start * 1000)),
        ];

        $requestDsn = config('metrichawk.dsn') . '/r/d';

        $client = new Client([ 'verify' => false ]);
        $client->post($requestDsn, [
            'json' => [
                'records' => $records
            ]
        ]);
    }
}
