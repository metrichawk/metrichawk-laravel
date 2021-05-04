<?php

namespace Metrichawk\MetrichawkLaravel\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Metrichawk\MetrichawkLaravel\Services\CollectorService;

class ExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 300;

    /**
     * @var string
     */
    private $dsn;

    /**
     * @var array
     */
    private $data;

    /**
     * ExportJob constructor.
     *
     * @param string $dsn
     * @param array  $data
     */
    public function __construct(string $dsn, array $data)
    {
        $this->dsn  = $dsn;
        $this->data = $data;
    }

    public function handle(): void
    {
        $this->data['queries'] = resolve(CollectorService::class)->formatQueryData($this->data['queries']);

        try {
            retry(5, function () {
                resolve(CollectorService::class)->sendData($this->dsn, $this->data);
            }, 500);
        } catch (Exception $e) {
        }
    }
}
