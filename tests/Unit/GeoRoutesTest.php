<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use LaraCrafts\GeoRoutes\Tests\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Illuminate\Http\Request;
use BadMethodCallException;

class GeoRoutesTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $router;

    protected $route;

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
     */
    public function orNotFoundCallbackThrowsExceptionForDeniedCountry()
    {
        $this->route->allowFrom('uk')->orNotFound();
        
        $response = $this->get('/foo/?country=us');
        $response->assertNotFound();
    }

    /**
     * @test
     */
    public function orRedirectToCallbackRedirectsToRoute()
    {
        $redirectRoute = $this->router->addRoute(['GET', 'HEAD'], '/redirect', function () {
            return "hi";
        })->name('redirect');

        $this->route->allowFrom('uk')->orRedirectTo('redirect');
        
        $response = $this->get('/foo/?country=us');
        $response->assertRedirect('/redirect');
    }

    /**
     * @test
     */
    public function orUnauthorizedCallbackThrowsException()
    {
        $this->route->allowFrom('uk')->orUnauthorized('redirect');
        $response = $this->get('/foo/?country=us');
        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function canChainWithRoute()
    {
        $this->route->allowFrom('uk')->orNotFound()->name('test');
        $response = $this->get('/foo/?country=us');
        $response->assertNotFound();
    }

    /**
     * @test
     *
     * @expectedException BadMethodCallException
     */
    public function throwsExceptionForInvalidCallback()
    {
        $this->route->allowFrom('us')->invalid();
    }
}
