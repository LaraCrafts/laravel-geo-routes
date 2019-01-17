<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use Illuminate\Support\Arr;
use LaraCrafts\GeoRoutes\Tests\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class GeoRoutesTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var \Illuminate\Routing\Router */
    protected $router;

    /** @var \Illuminate\Routing\Route */
    protected $route;

    /** @var \Mockery\MockInterface */
    protected $controller;

    /** @var \Mockery\MockInterface */
    protected $location;

    public function setUp()
    {
        parent::setUp();
        $this->router = $this->app->get('router');
        $this->route = $this->router->addRoute(['GET', 'HEAD'], '/foo', 'BarController@baz')->name('test');
        $this->controller = Mockery::mock('BarController');
        $this->location = Mockery::mock('overload:Location');
    }

    public function tearDown()
    {
        Mockery::close();
    }

    /**
     * @test
     * @small
     */
    public function macrosAddMiddleware()
    {
        $this->route->allowFrom('nl');
        $this->assertEquals('geo:allow,NL', Arr::last($this->route->middleware()));

        $this->route->from('tn');
        $this->assertEquals('geo:allow,TN', Arr::last($this->route->middleware()));

        $this->route->from('be')->allow();
        $this->assertEquals('geo:allow,BE', Arr::last($this->route->middleware()));

        $this->route->from('jp')->deny();
        $this->assertEquals('geo:deny,JP', Arr::last($this->route->middleware()));

        $this->route->denyFrom('us');
        $this->assertEquals('geo:deny,US', Arr::last($this->route->middleware()));
    }

    /**
     * @test
     * @small
     */
    public function orNotFoundCallbackThrowsExceptionForDeniedCountry()
    {
        $this->route->allowFrom('gb')->orNotFound();

        $this->location->shouldReceive('get')
            ->once()
            ->andReturn(json_decode('{"countryCode": "us"}'));

        $response = $this->get('/foo');

        $response->assertStatus(404);
    }

    /**
     * @test
     * @small
     */
    public function orRedirectToCallbackRedirectsToRoute()
    {
        $redirectRoute = $this->router->addRoute(['GET', 'HEAD'], '/redirect', function () {
            return 'hi';
        })->name('redirect');

        $this->location->shouldReceive('get')
            ->once()
            ->andReturn(json_decode('{"countryCode": "us"}'));

        $this->route->allowFrom('gb')->orRedirectTo('redirect');

        $response = $this->get('/foo');
        $response->assertRedirect('/redirect');
    }

    /**
     * @test
     * @small
     */
    public function orUnauthorizedCallbackThrowsException()
    {
        $this->location->shouldReceive('get')
            ->once()
            ->andReturn(json_decode('{"countryCode": "US"}'));

        $this->route->allowFrom('gb')->orUnauthorized('redirect');
        $response = $this->get('/foo');
        $response->assertStatus(401);
    }

    /**
     * @test
     * @small
     */
    public function canChainWithRoute()
    {
        $this->route->allowFrom('gb')->orNotFound()->name('test');

        $this->location->shouldReceive('get')
            ->once()
            ->andReturn(json_decode('{"countryCode": "us"}'));

        $response = $this->get('/foo');


        $response->assertStatus(404);
    }

    /**
     * @test
     * @small
     * @expectedException \BadMethodCallException
     */
    public function throwsExceptionForInvalidCallback()
    {
        $this->route->allowFrom('us')->invalid();
    }
}
