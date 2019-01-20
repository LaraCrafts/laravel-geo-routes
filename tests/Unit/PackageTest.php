<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use LaraCrafts\GeoRoutes\GeoRoute;
use LaraCrafts\GeoRoutes\Support\Facade;
use LaraCrafts\GeoRoutes\Tests\TestCase;

class PackageTest extends TestCase
{
    /** @var \Illuminate\Routing\Router */
    protected $router;

    protected function routeMacrosAreSupported()
    {
        return version_compare($this->app->version(), '5.5.0', '>=');
    }

    public function setUp()
    {
        parent::setUp();
        $this->router = $this->app->make('router');
    }

    public function testFacade()
    {
        $this->assertInstanceOf(GeoRoute::class, Facade::get('/foo', 'BarController@baz'));
    }

    public function testMacros()
    {
        if (!$this->routeMacrosAreSupported()) {
            $this->markTestSkipped('Route macros are not supported in this version of Laravel');
        }

        $this->assertInstanceOf(GeoRoute::class, $this->router->get('/foo', 'BarController@baz')->from('it'));
        $this->assertInstanceOf(GeoRoute::class, $this->router->get('/foo', 'BarController@baz')->allowFrom('ch'));
        $this->assertInstanceOf(GeoRoute::class, $this->router->get('/foo', 'BarController@baz')->denyFrom('ru'));
    }
}
