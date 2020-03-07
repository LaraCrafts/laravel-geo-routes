<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use Mockery;
use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel;
use LaraCrafts\GeoRoutes\Tests\TestCase;
use LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks;
use LaraCrafts\GeoRoutes\Support\Facades\CallbackRegistrar;

class GeoMiddlewareTest extends TestCase
{
    /** @var \Illuminate\Contracts\Http\Kernel */
    protected $kernel;

    /** @var \Mockery\MockInterface */
    protected $location;

    /** @var \Illuminate\Http\Request */
    protected $request;

    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']['geo-routes.global.countries'] = ['ch'];
        $this->app->make('router')->get('/', function () {
            return 'Hello world';
        });

        $this->kernel = $this->app->make(Kernel::class);
        $this->location = Mockery::mock('overload:Location');
        $this->request = $this->app->make(Request::class);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testIfDisallowedCountryIsBlocked()
    {
        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn((object)['countryCode' => 'ch']);

        $response = $this->kernel->handle($this->request);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testIfAllowedCountryIsLetThrough()
    {
        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn((object)['countryCode' => 'jm']);

        $response = $this->kernel->handle($this->request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testIfMiddlewareExecutesDefaultCallback()
    {
        $callbacks = Mockery::mock(Callbacks::class);
        $callbacks->shouldReceive('foo')->once()->with('bar')->andReturn('foo');

        CallbackRegistrar::setDefault([$callbacks, 'foo'], 'bar');

        $this->location->shouldReceive('get')
            ->once()
            ->andReturn((object)['countryCode' => 'ch']);

        $response = $this->kernel->handle($this->request);

        $this->assertEquals('foo', $response);
    }
}
