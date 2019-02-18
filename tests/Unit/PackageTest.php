<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use LaraCrafts\GeoRoutes\GeoGroup;
use LaraCrafts\GeoRoutes\GeoRoute;
use LaraCrafts\GeoRoutes\Tests\TestCase;

class PackageTest extends TestCase
{
    /** @var \Illuminate\Routing\Router */
    protected $router;

    public function setUp()
    {
        parent::setUp();
        $this->router = $this->app->make('router');
    }

    public function testMacros()
    {
        $this->assertInstanceOf(
                GeoGroup::class, $this->router->geogroup(['prefix' => 'foo'],

                function() {
                    $this->router->get('/foo', 'BarController@baz');
                })

                ->allowFrom('it')
        );

        $this->assertInstanceOf(GeoRoute::class, $this->router->get('/foo', 'BarController@baz')->allowFrom('ch'));
        $this->assertInstanceOf(GeoRoute::class, $this->router->get('/foo', 'BarController@baz')->denyFrom('ru'));
    }
}
