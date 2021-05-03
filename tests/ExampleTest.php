<?php

namespace Metrichawk\MetrichawkLaravel\Tests;

use Orchestra\Testbench\TestCase;
use Metrichawk\MetrichawkLaravel\MetrichawkLaravelServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [MetrichawkLaravelServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
