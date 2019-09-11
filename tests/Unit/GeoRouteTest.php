<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use LaraCrafts\GeoRoutes\GeoRoute;
use LaraCrafts\GeoRoutes\Tests\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class GeoRouteTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var \Mockery\MockInterface */
    protected $location;

    /** @var \Illuminate\Routing\Route */
    protected $route;

    /** @var \Illuminate\Routing\Router */
    protected $router;

    public function setUp(): void
    {
        parent::setUp();

        $this->location = Mockery::mock('overload:Location');
        $this->router = $this->app->make('router');
        $this->route = $this->router->get('/foo', ['uses' => '\LaraCrafts\GeoRoutes\Tests\Mocks\MockController@index', 'as' => 'qux']);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testIfMiddlewareIsApplied()
    {
        (new GeoRoute($this->route, ['nl'], 'allow'));
        $this->assertEquals('geo', last($this->route->middleware()));

        (new GeoRoute($this->route, ['tn'], 'deny'));
        $this->assertEquals('geo', last($this->route->middleware()));
    }


    public function testDefaultCallback()
    {
        (new GeoRoute($this->route, ['kr'], 'allow'));

        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn((object)['countryCode' => 'ca']);

        $response = $this->get('/foo');

        $response->assertStatus(401);
    }

    public function testOrNotFoundCallback()
    {
        (new GeoRoute($this->route, ['gb'], 'allow'))->orNotFound();

        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn((object)['countryCode' => 'us']);

        $response = $this->get('/foo');

        $response->assertStatus(404);
    }

    public function testOrRedirectCallback()
    {
        (new GeoRoute($this->route, ['uk'], 'allow'))->orRedirectTo('grault');

        $this->router->get('/quux', ['uses' => 'CorgeController@uier', 'as' => 'grault']);

        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn((object)['countryCode' => 'fr']);

        $response = $this->get('/foo');

        $response->assertRedirect('/quux');
    }
}
