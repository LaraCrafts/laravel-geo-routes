<?php

namespace LaraCrafts\GeoRoutes\Tests;

use LaraCrafts\GeoRoutes\GeoRoutesServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            GeoRoutesServiceProvider::class,
        ];
    }
}
