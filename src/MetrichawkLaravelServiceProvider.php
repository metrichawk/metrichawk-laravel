<?php

namespace Metrichawk\MetrichawkLaravel;

use Illuminate\Contracts\Http\Kernel;
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
        $this->ensureLaravelConstantExists();

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
     * @throws Throwable
     */
    private function ensureLaravelConstantExists()
    {
        throw_if(constant('LARAVEL_START') === null, new MetrichawkLaravelException('Constant LARAVEL_START not defined in index.php'));
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
