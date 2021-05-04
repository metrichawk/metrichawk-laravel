<?php

namespace Metrichawk\MetrichawkLaravel;

use Illuminate\Support\ServiceProvider;
use Throwable;

class MetrichawkLaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @throws Throwable
     */
    public function boot()
    {
        if (config('metrichawk.enabled') === false) {
            return;
        }

        if ($this->ensureDsnExists() === false) {
            return;
        }

        if ($this->app->runningInConsole() === true) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('metrichawk.php'),
            ], 'config');

            return;
        }

        MetrichawkLaravel::start($this->app);
    }

    /**
     * @throws Throwable
     */
    private function ensureDsnExists()
    {
        return in_array(config('metrichawk.dsn'), [null, '']) === false;
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'metrichawk');

        $this->app->singleton('metrichawk-laravel', function () {
            return new MetrichawkLaravel;
        });
    }
}
