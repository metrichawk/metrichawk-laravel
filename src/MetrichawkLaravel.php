<?php

namespace Metrichawk\MetrichawkLaravel;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Metrichawk\MetrichawkLaravel\Traits\RegistersWatchers;

class MetrichawkLaravel
{
    use RegistersWatchers;

    public static function start($app)
    {
        if (! config('metrichawk.enabled')) {
            return;
        }

        static::registerWatchers($app);
    }
}
