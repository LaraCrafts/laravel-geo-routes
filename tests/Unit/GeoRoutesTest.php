<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

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

    public function setUp()
    {
        parent::setUp();
        $this->router = $this->app->get('router');
        $this->route = $this->router->addRoute(['GET', 'HEAD'], '/foo', 'BarController@baz')->name('test');
        $this->controller = Mockery::mock('BarController');
    }

    public function tearDown()
    {
        Mockery::close();
    }

    /**
     * @test
     * @small
     */
    public function MacrosAddMiddleware()
    {
        $macros = ['from', 'allowFrom', 'denyFrom'];

        foreach ($macros as $macro) {
            $this->route->{$macro}('us');
            $this->assertRegExp('/geo:(allow|deny),us/', serialize($this->route->middleware()));
        }
    }

    /**
     * @test
     * @small
     */
    public function orNotFoundCallbackThrowsExceptionForDeniedCountry()
    {
        $this->route->allowFrom('gb')->orNotFound();

        $response = $this->get('/foo/?country=us');
        $response->assertNotFound();
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

        $this->route->allowFrom('gb')->orRedirectTo('redirect');

        $response = $this->get('/foo/?country=us');
        $response->assertRedirect('/redirect');
    }

    /**
     * @test
     * @small
     */
    public function orUnauthorizedCallbackThrowsException()
    {
        $this->route->allowFrom('gb')->orUnauthorized('redirect');
        $response = $this->get('/foo/?country=us');
        $response->assertStatus(401);
    }

    /**
     * @test
     * @small
     */
    public function canChainWithRoute()
    {
        $this->route->allowFrom('gb')->orNotFound()->name('test');
        $response = $this->get('/foo/?country=us');
        $response->assertNotFound();
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

    /**
     * @test
     * @small
     * @expectedException \InvalidArgumentException
     */
    public function throwsExceptionForInvalidCountryCode()
    {
        $this->route->allowFrom('INVALID');
    }
}
