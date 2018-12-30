<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use LaraCrafts\GeoRoutes\GeoRoutesServiceProvider;
use Orchestra\Testbench\TestCase;

class ExampleTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [GeoRoutesServiceProvider::class];
    }

    public function testServiceProviderWasRun()
    {
        # This is just a simple example, replace this with some real tests at some point
        $this->assertArrayHasKey(GeoRoutesServiceProvider::class, $this->app->getLoadedProviders());
    }
}
