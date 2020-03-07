<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use LaraCrafts\GeoRoutes\Http\Middleware\GeoRoutesMiddleware;
use LaraCrafts\GeoRoutes\Support\Facades\CallbackRegistrar;
use LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks;
use LaraCrafts\GeoRoutes\Tests\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

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
        $callbacks = Mockery::mock(Callbacks::class);
        $callbacks->shouldReceive('foo')
            ->once()
            ->with('arg')
            ->andReturn('foo');

        $this->location->shouldReceive('get')
            ->once()
            ->andReturn((object)['countryCode' => 'ca']);

        $callback = [[$callbacks, 'foo'], ['arg']];

        $this->setGeoConstraint('allow', 'us', $callback);

        $output = $this->middleware->handle($this->request, $this->next);

        $this->assertEquals('foo', $output);
    }

    /** @test */
    public function middlewareThrowsExceptionIfTheGeoAttributeIsInvalid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The GeoRoute constraint is invalid.');

        $this->request->shouldReceive('route')
                    ->once()
                    ->andReturn($this->route);

        $this->route->shouldReceive('getAction')
                    ->with('geo')
                    ->once()
                    ->andReturn([]);

        $this->middleware->handle($this->request, $this->next);
    }

    /** @test */
    public function middlewareExecutesDefaultCallbackIfNoCallbackIsFound()
    {
        $callbacks = Mockery::mock(Callbacks::class);
        $callbacks->shouldReceive('foo')->once()->with('bar');

        CallbackRegistrar::setDefault([$callbacks, 'foo'], 'bar');

        $this->location->shouldReceive('get')
            ->once()
            ->andReturn((object)['countryCode' => 'ca']);

        $this->setGeoConstraint('deny', 'ca');

        $this->middleware->handle($this->request, $this->next);
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
                        'callback' => $callback,
                    ]);
    }
}
