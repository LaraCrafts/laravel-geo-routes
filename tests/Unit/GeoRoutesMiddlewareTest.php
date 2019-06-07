<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use Illuminate\Http\Request;
use LaraCrafts\GeoRoutes\Http\Middleware\GeoRoutesMiddleware;
use LaraCrafts\GeoRoutes\Tests\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Illuminate\Routing\Route;
use Stevebauman\Location\Facades\Location;

class GeoRoutesMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var \LaraCrafts\GeoRoutes\Http\Middleware\GeoRoutesMiddleware */
    protected $middleware;

    /** @var \Closure */
    protected $next;

    /** @var \Mockery\MockInterface */
    protected $request;

    /** @var \Mockery\MockInterface */
    protected $location;

    /** @var \Mockery\MockInterface */
    protected $route;

    public function setUp(): void
    {
        parent::setUp();

        $this->middleware = new GeoRoutesMiddleware();
        $this->next = function () {
            return 'User got through';
        };
        $this->request = Mockery::mock(Request::class);
        $this->route = Mockery::mock(Route::class);
        $this->location = Mockery::mock('overload:Location');
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    /** @test */
    public function denyDeniesCountry()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->location->shouldReceive('get')
            ->once()
            ->andReturn((object)['countryCode' => 'us']);

        $this->setGeoConstraint('deny', 'us');

        $this->middleware->handle($this->request, $this->next);
    }

    /** @test */
    public function middlewareAllowsAccess()
    {
        $this->setGeoConstraint('allow', 'us');

        $this->location->shouldReceive('get')
            ->once()
            ->andReturn((object)['countryCode' => 'us']);


        $output = $this->middleware->handle($this->request, $this->next);
        $this->assertEquals('User got through', $output);
    }

    /** @test */
    public function middlewareExecutesCallback()
    {
        $mockClass = Mockery::mock('alias:mockClass');
        $mockClass->shouldReceive('callback')
            ->once()
            ->with('arg')
            ->andReturn('MockCallback');

        $this->location->shouldReceive('get')
            ->once()
            ->andReturn((object)['countryCode' => 'ca']);

        $callback = ['mockClass::callback', ['arg']];

        $this->setGeoConstraint('allow', 'us', $callback);

        $output = $this->middleware->handle($this->request, $this->next);

        $this->assertEquals('MockCallback', $output);
    }

    /**
     * Set the route geo constraint.
     *
     * @param string $strategy
     * @param array|string $countries
     * @param array $callback
     *
     * @return void
     */
    protected function setGeoConstraint(string $strategy, $countries, array $callback = null)
    {
        $this->request->shouldReceive('route')
                    ->once()
                    ->andReturn($this->route);

        $this->route->shouldReceive('getAction')
                    ->with('geo')
                    ->once()
                    ->andReturn([
                        'strategy' => $strategy,
                        'countries' => (array)$countries,
                        'callback' => $callback
                    ]);
    }
}
