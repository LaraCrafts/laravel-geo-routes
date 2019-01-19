<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use Illuminate\Support\Arr;
use LaraCrafts\GeoRoutes\Tests\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class MacroTest extends TestCase
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

        if (!$this->routeMacrosAreSupported()) {
            $this->markTestSkipped();
        }

        $this->router = $this->app->get('router');
        $this->route = $this->router->get('/foo', 'BarController@baz')->name('test');
        $this->controller = Mockery::mock('BarController');
        $this->location = Mockery::mock('overload:Location');
    }

    protected function routeMacrosAreSupported()
    {
        return version_compare($this->app->version(), '5.5.0', '>=');
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

        $this->assertTrue($this->get('/foo')->isNotFound());
    }

    /**
     * @test
     * @small
     */
    public function orRedirectToCallbackRedirectsToRoute()
    {
        $this->route->allowFrom('gb')->orRedirectTo('redirect');
        $this->router->get('/redirected', 'BarController@baz')->name('redirect');

        $this->location->shouldReceive('get')
            ->once()
            ->andReturn(json_decode('{"countryCode": "us"}'));

        $response = $this->get('/foo');
        $url = $this->app->make('url');

        $this->assertTrue($response->isRedirect());
        $this->assertEquals($url->to('/redirected'), $url->to($response->headers->get('Location')));
    }

    /**
     * @test
     * @small
     */
    public function orUnauthorizedCallbackThrowsException()
    {
        $this->route->allowFrom('gb')->orUnauthorized('redirect');

        $this->location->shouldReceive('get')
            ->once()
            ->andReturn(json_decode('{"countryCode": "US"}'));

        $this->assertEquals(401, $this->get('/foo')->getStatusCode());
    }

    /**
     * @test
     * @small
     */
    public function canChainWithRoute()
    {
        $this->route->allowFrom('gb')->orNotFound();

        $this->location->shouldReceive('get')
            ->once()
            ->andReturn(json_decode('{"countryCode": "us"}'));

        $this->assertTrue($this->get('/foo')->isNotFound());
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
