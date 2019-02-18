<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use Mockery;
use Illuminate\Http\Request;
use LaraCrafts\GeoRoutes\Tests\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use LaraCrafts\GeoRoutes\Http\Middleware\GeoRoutesMiddleware;

class GeoRoutesMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var \LaraCrafts\GeoRoutes\Http\Middleware\GeoRoutesMiddleware */
    protected $middleware;

    /** @var \Closure */
    protected $next;

    /** @var \Illuminate\Http\Request */
    protected $request;

    /** @var \Mockery\MockInterface */
    protected $location;

    /** @var \Illuminate\Routing\Router */
    protected $router;

    /** @var \Illuminate\Routing\Route */
    protected $route;

    protected $routeGroup;

    public function setUp()
    {
        parent::setUp();

        $this->middleware = new GeoRoutesMiddleware();
        $this->next = function () {
            return 'User got through';
        };
        $this->request = new Request();

        $this->router = $this->app->make('router');

        $this->route = $this->router->get('/foo', ['uses' => '\LaraCrafts\GeoRoutes\Tests\Mocks\MockController@index', 'as' => 'qux']);

        $this->request->setRouteResolver(function() {
            return $this->route->bind($this->request);
        });

        $this->location = Mockery::mock('overload:Location');
    }

    public function tearDown()
    {
        Mockery::close();
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function denyDeniesCountry()
    {
        $this->route->denyFrom('us');

        $this->location->shouldReceive('get')
            ->once()
            ->andReturn((object)['countryCode' => 'US']);

        $this->middleware->handle($this->request, $this->next);
    }

    /** @test */
    public function middlewareAllowsAccess()
    {
        $this->route->allowFrom('us');

        $this->location->shouldReceive('get')
            ->once()
            ->andReturn((object)['countryCode' => 'US']);

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
            ->andReturn((object)['countryCode' => 'CA']);

        #TODO: Parse callables
        $this->route->denyFrom('ca')->or(function() use ($mockClass) {
            return $mockClass::callback('arg');
        });

        $output = $this->middleware->handle($this->request, $this->next);

        $this->assertEquals('MockCallback', $output);
    }
}
