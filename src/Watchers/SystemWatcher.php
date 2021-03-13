<?php

namespace Metrichawk\MetrichawkLaravel\Watchers;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Str;
use Metrichawk\MetrichawkLaravel\MetrichawkLaravel;

class SystemWatcher extends Watcher
{
    protected $isLinuxSystem;

    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     */
    public function register($app)
    {
        $this->isLinuxSystem = is_readable("/proc/meminfo");

        $app['events']->listen(RequestHandled::class, [$this, 'recordSystem']);
    }

    /**
     * @param RequestHandled $event
     */
    public function recordSystem(RequestHandled $event)
    {
        $data = [
            'cpu_load_last_minute' => null, // only works on Linux System
            'memory_load' => null, // only works on Linux System
            'php_memory_peak_usage' => memory_get_peak_usage(true), // / 1024  / 1025 for Mb
            'disk_load' => round(100 - ((disk_free_space('.') / disk_total_space('.')) * 100), 2),
        ];

        if ($this->isLinuxSystem === true) {
            $data['memory_load'] = $this->getSystemMemLoad();
            $data['cpu_load_last_minute'] = sys_getloadavg()[0];
        }

        MetrichawkLaravel::recordSystem($data);
    }

    /**
     * @return float|null
     */
    protected function getSystemMemLoad(): ?float
    {
        $meminfo = @file_get_contents("/proc/meminfo");

        if ($meminfo) {
            $data = explode("\n", $meminfo);
            $meminfo = [];

            foreach ($data as $line) {
                if (strpos($line, ':') !== false) {
                    list($key, $val) = explode(":", $line);

                    if (Str::startsWith($key, 'Mem') === false) {
                        continue;
                    }

                    $val = trim($val);
                    $val = preg_replace('/ kB$/', '', $val);

                    if (is_numeric($val)) {
                        $val = intval($val);
                    }

                    $meminfo[$key] = $val;
                }
            }

            if (isset($meminfo['MemAvailable']) === false) {
                return null;
            }

            if (isset($meminfo['MemTotal']) === false) {
                return null;
            }

            return 100 - round((($meminfo['MemAvailable']) / $meminfo['MemTotal']) * 100, 2);
        }

        return null;
    }
}
