<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use LaraCrafts\GeoRoutes\GeoRoutesServiceProvider;
use LaraCrafts\GeoRoutes\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function testServiceProviderWasRegistered()
    {
        $this->assertArrayHasKey(GeoRoutesServiceProvider::class, $this->app->getLoadedProviders());
    }
}
