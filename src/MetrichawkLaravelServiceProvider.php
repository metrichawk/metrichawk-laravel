<?php

namespace Metrichawk\MetrichawkLaravel;

use Illuminate\Support\ServiceProvider;
use Metrichawk\MetrichawkLaravel\Exceptions\MetrichawkLaravelException;
use Throwable;

class MetrichawkLaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     * @throws Throwable
     */
    public function boot()
    {
        if (config('metrichawk.enabled') === false) {
            return;
        }

        $this->ensureDsnExists();

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
        throw_if(in_array(config('metrichawk.dsn'), [null, '']) === true, new MetrichawkLaravelException('METRICHAWK_DSN environment variable is not defined.'));
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
