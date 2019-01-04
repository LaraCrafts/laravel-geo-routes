<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use LaraCrafts\GeoRoutes\Tests\TestCase;

class GeoRoutesTest extends TestCase
{
    /**
     * Test if the 'geo' middleware is applied upon destruction of the GeoRoutes object.
     */
    public function testIfAllowFromAddsMiddleware()
    {
        $route = $this->app->get('router')->addRoute(['GET', 'HEAD'], '/foo', 'BarController@baz');
        $route->from('us')->allow();

        $this->assertContains('geo:allow,us', $route->middleware());
    }
}
