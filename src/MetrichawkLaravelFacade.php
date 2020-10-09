<?php

namespace Metrichawk\MetrichawkLaravel;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Metrichawk\MetrichawkLaravel\Skeleton\SkeletonClass
 */
class MetrichawkLaravelFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'metrichawk-laravel';
    }
}
