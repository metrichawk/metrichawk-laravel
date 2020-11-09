<?php

namespace Metrichawk\MetrichawkLaravel\Traits;

use Illuminate\Support\Arr;
use Metrichawk\MetrichawkLaravel\Watchers\MainWatcher;
use Metrichawk\MetrichawkLaravel\Watchers\RequestWatcher;

trait RegistersWatchers
{
    /**
     * The class names of the registered watchers.
     *
     * @var array
     */
    protected static $watchers = [];

    /**
     * Determine if a given watcher has been registered.
     *
     * @param string $class
     * @return bool
     */
    public static function hasWatcher($class)
    {
        return in_array($class, static::$watchers);
    }

    /**
     * Register the configured Telescope watchers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected static function registerWatchers($app)
    {
        $watchers = config('metrichawk.watchers');

        // Necessary watchers
        $watchers = Arr::prepend($watchers, ['enabled' => true], RequestWatcher::class);

        foreach ($watchers as $key => $watcher) {
            if (is_string($key) && $watcher === false) {
                continue;
            }

            if (is_array($watcher) && !($watcher['enabled'] ?? true)) {
                continue;
            }

            $watcher = $app->make(is_string($key) ? $key : $watcher, [
                'options' => is_array($watcher) ? $watcher : [],
            ]);

            static::$watchers[] = get_class($watcher);

            $watcher->register($app);
        }
    }
}
