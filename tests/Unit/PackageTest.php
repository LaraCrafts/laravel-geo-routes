<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use LaraCrafts\GeoRoutes\CallbackRegistrar;
use LaraCrafts\GeoRoutes\GeoGroup;
use LaraCrafts\GeoRoutes\GeoRoute;
use LaraCrafts\GeoRoutes\Tests\TestCase;

class PackageTest extends TestCase
{
    /** @var \Illuminate\Routing\Router */
    protected $router;

    public function setUp(): void
    {
        parent::setUp();
        $this->router = $this->app->make('router');
    }

    public function testMacros()
    {
        $this->assertInstanceOf(GeoRoute::class, $this->router->get('/foo', 'BarController@baz')->from('it'));
        $this->assertInstanceOf(GeoRoute::class, $this->router->get('/foo', 'BarController@baz')->allowFrom('ch'));
        $this->assertInstanceOf(GeoRoute::class, $this->router->get('/foo', 'BarController@baz')->denyFrom('ru'));
        $this->assertInstanceOf(GeoGroup::class, $this->router->geo([], function () {
        })->allowFrom('gb'));
    }

    public function testBindings()
    {
        $this->assertInstanceOf(CallbackRegistrar::class, $this->app->make('georoutes.callbacks'));
    }
}
