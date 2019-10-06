<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use LaraCrafts\GeoRoutes\GeoGroup;
use LaraCrafts\GeoRoutes\Tests\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class GeoGroupTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var \Mockery\MockInterface */
    protected $location;

    /** @var \Illuminate\Routing\Route */
    protected $route;

    /** @var \Illuminate\Routing\Router */
    protected $router;

    /** @var \LaraCrafts\GeoRoutes\GeoGroup */
    protected $group;

    /** @var array */
    protected $testingRoutes;

    public function setUp(): void
    {
        parent::setUp();

        $this->location = Mockery::mock('overload:Location');
        $this->router = $this->app->make('router');
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testIfMiddlewareIsApplied()
    {
        $this->getTestingGeoGroup()->allowFrom('us', 'ca');

        foreach ($this->testingRoutes as $route) {
            $this->assertEquals('geo', last($route->middleware()));
        }
    }

    public function testDefaultCallback()
    {
        $this->getTestingGeoGroup()->allowFrom('kr');

        $this->location
            ->shouldReceive('get')
            ->twice()
            ->andReturn((object)['countryCode' => 'ca']);

        foreach ($this->testingRoutes as $route) {
            $response = $this->get($route->uri());

            $response->assertStatus(401);
        }
    }

    public function testOrNotFoundCallback()
    {
        $this->getTestingGeoGroup()->allowFrom('gb')->orNotFound();

        $this->location
            ->shouldReceive('get')
            ->twice()
            ->andReturn((object)['countryCode' => 'us']);

        foreach ($this->testingRoutes as $route) {
            $response = $this->get($route->uri());

            $response->assertStatus(404);
        }
    }

    public function testOrRedirectCallback()
    {
        $this->getTestingGeoGroup()->allowFrom('uk')->orRedirectTo('grault');

        $this->router->get('/quux', ['uses' => 'CorgeController@uier', 'as' => 'grault']);

        $this->location
            ->shouldReceive('get')
            ->twice()
            ->andReturn((object)['countryCode' => 'fr']);

        foreach ($this->testingRoutes as $route) {
            $response = $this->get($route->uri());

            $response->assertRedirect('/quux');
        }
    }

    public function testIfAttributeIsApplied()
    {
        $this->getTestingGeoGroup()->allowFrom('uk')->attribute('prefix', 'grault');

        foreach ($this->testingRoutes as $route) {
            $routeAction = $route->getAction();

            $this->assertArrayHasKey('prefix', $routeAction);
            $this->assertEquals($routeAction['prefix'] ?? '', 'grault');
        }
    }

    public function testIfAttributeIsAppliedDynamically()
    {
        $this->getTestingGeoGroup()->allowFrom('uk')->prefix('grault');

        foreach ($this->testingRoutes as $route) {
            $routeAction = $route->getAction();

            $this->assertArrayHasKey('prefix', $routeAction);
            $this->assertEquals($routeAction['prefix'] ?? '', 'grault');
        }
    }

    /**
     * Get the testing geogroup instance.
     *
     * @return \LaraCrafts\GeoRoutes\GeoGroup
     */
    protected function getTestingGeoGroup()
    {
        return new GeoGroup([], function () {
            $this->testingRoutes[] = $this->router->get(
                '/foo',
                [
                    'uses' => '\LaraCrafts\GeoRoutes\Tests\Mocks\MockController@index',
                    'as' => 'foo',
                ]
            );

            $this->testingRoutes[] = $this->router->get(
                '/bar',
                [
                    'uses' => '\LaraCrafts\GeoRoutes\Tests\Mocks\MockController@index',
                    'as' => 'bar',
                ]
            );
        });
    }
}
