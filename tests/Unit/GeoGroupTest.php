<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use LaraCrafts\GeoRoutes\GeoGroup;
use LaraCrafts\GeoRoutes\Tests\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class GeoGroupTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var \Mockery\MockInterface */
    protected $location;

    /** @var \Illuminate\Routing\Route */
    protected $route;

    /** @var \Illuminate\Routing\Router */
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $this->location = Mockery::mock('overload:Location');
        $this->router = $this->app->make('router');
        $this->route = $this->router->get('/foo', ['uses' => '\LaraCrafts\GeoRoutes\Tests\Mocks\MockController@index', 'as' => 'qux']);
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testDefaultCallback()
    {
        (new GeoGroup(['prefix' => 'foo'], function() {
            $this->router->get('/bar', '\LaraCrafts\GeoRoutes\Tests\Mocks\MockController@index');
            $this->router->get('/baz', '\LaraCrafts\GeoRoutes\Tests\Mocks\MockController@index');
        }))->denyFrom('US');

        $this->location
            ->shouldReceive('get')
            ->twice()
            ->andReturn((object)['countryCode' => 'US']);

        $response = $this->get('/foo/bar');

        $response->assertStatus(401);

        $response = $this->get('/foo/baz');

        $response->assertStatus(401);
    }

    public function testOrNotFoundCallback()
    {
        (new GeoGroup(['prefix' => 'foo'], function() {
            $this->router->get('/bar', '\LaraCrafts\GeoRoutes\Tests\Mocks\MockController@index');
            $this->router->get('/baz', '\LaraCrafts\GeoRoutes\Tests\Mocks\MockController@index');
        }))->denyFrom('US')->orNotFound();


        $this->location
            ->shouldReceive('get')
            ->twice()
            ->andReturn((object)['countryCode' => 'US']);


        $response = $this->get('/foo/bar');

        $response->assertStatus(404);

        $response = $this->get('/foo/baz');

        $response->assertStatus(404);
    }

    public function testOrRedirectCallback()
    {
        (new GeoGroup(['prefix' => 'foo'], function() {
            $this->router->get('/bar', '\LaraCrafts\GeoRoutes\Tests\Mocks\MockController@index');
            $this->router->get('/baz', '\LaraCrafts\GeoRoutes\Tests\Mocks\MockController@index');
        }))->denyFrom('fr')->orRedirectTo('grault');

        $this->router->get('/quux', ['uses' => 'CorgeController@uier', 'as' => 'grault']);

        $this->location
            ->shouldReceive('get')
            ->twice()
            ->andReturn((object)['countryCode' => 'FR']);

        $response = $this->get('/foo/bar');

        $response->assertRedirect('/quux');

        $response = $this->get('/foo/baz');

        $response->assertRedirect('/quux');
    }

    public function testOrClosureCallback()
    {
        (new GeoGroup(['prefix' => 'foo'], function() {
            $this->router->get('/bar', '\LaraCrafts\GeoRoutes\Tests\Mocks\MockController@index');
            $this->router->get('/baz', '\LaraCrafts\GeoRoutes\Tests\Mocks\MockController@index');
        }))->allowFrom('fr')->or(function() {
            abort(303);
        });

        $this->location
            ->shouldReceive('get')
            ->twice()
            ->andReturn((object)['countryCode' => 'us']);

        $response = $this->get('/foo/bar');
        $response->assertStatus(303);

        $response = $this->get('/foo/baz');
        $response->assertStatus(303);
    }
}
