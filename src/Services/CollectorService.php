<?php

namespace Metrichawk\MetrichawkLaravel\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;

class CollectorService
{
    /**
     * @param string $dsn
     * @param array  $data
     */
    public function sendData(string $dsn, array $data): void
    {
        $client = new Client([
            'verify'  => false,
            'timeout' => 10,
        ]);

        try {
            $client->post($dsn, [
                'json' => $data,
            ]);
        } catch (GuzzleException $e) {
            report($e);
        } catch (Exception $exception) {
            report($exception);
        }
    }

    /**
     * @param array $queryData
     *
     * @return array
     */
    public function formatQueryData(array $queryData): array
    {
        $data = [];

        $queriesByConnection = collect($queryData)->groupBy('connection_name');

        $queriesByConnection->each(function (Collection $queries, string $connectionName) use (&$data) {
            $sqlDuration = $queries->sum('duration');

            $sqlDuplicationCount = $queries->countBy('hash')->sum(function ($count) {
                if ($count > 1) {
                    return $count;
                }

                return 0;
            });

            $data[] = [
                'connection_name'   => $connectionName,
                'duration'          => $sqlDuration,
                'duplication_count' => $sqlDuplicationCount,
                'request_count'     => $queries->count(),
            ];
        });

        return $data;
    }
}
