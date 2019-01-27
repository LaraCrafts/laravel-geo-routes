<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use LaraCrafts\GeoRoutes\Tests\TestCase;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GeoMiddlewareTest extends TestCase
{
    /** @var \Illuminate\Contracts\Http\Kernel */
    protected $kernel;

    /** @var \Mockery\MockInterface */
    protected $location;

    /** @var \Illuminate\Http\Request */
    protected $request;

    public function setUp()
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

    public function tearDown()
    {
        Mockery::close();
    }

    public function testIfDisallowedCountryIsBlocked()
    {
        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn((object)['countryCode' => 'ch']);

        try {
            $response = $this->kernel->handle($this->request);
        } catch (HttpException $response) {
            // Laravel will throw in versions 5.0 - 5.2
        }

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
}
