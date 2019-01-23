<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use LaraCrafts\GeoRoutes\GeoRoute;
use LaraCrafts\GeoRoutes\Tests\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class GeoRouteTest extends TestCase
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

    public function testIfMiddlewareIsApplied()
    {
        (new GeoRoute($this->route, ['nl'], 'allow'));
        $this->assertEquals('geo:allow,NL', last($this->route->middleware()));

        (new GeoRoute($this->route, ['tn'], 'allow'))->allow();
        $this->assertEquals('geo:allow,TN', last($this->route->middleware()));

        (new GeoRoute($this->route, ['be'], 'deny'))->allow();
        $this->assertEquals('geo:allow,BE', last($this->route->middleware()));

        (new GeoRoute($this->route, ['jp'], 'allow'))->deny();
        $this->assertEquals('geo:deny,JP', last($this->route->middleware()));

        (new GeoRoute($this->route, ['us'], 'deny'))->deny();
        $this->assertEquals('geo:deny,US', last($this->route->middleware()));
    }

    /**
     * @group new_versions
     */
    public function testDefaultCallback()
    {
        (new GeoRoute($this->route, ['kr'], 'allow'));

        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn(json_decode('{"countryCode": "ca"}'));

        $this->assertEquals(401, $this->get('/foo')->getStatusCode());
    }

    /**
     * @group new_versions
     */
    public function testOrNotFoundCallback()
    {
        (new GeoRoute($this->route, ['gb'], 'allow'))->orNotFound();

        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn(json_decode('{"countryCode": "us"}'));

        $this->assertTrue($this->get('/foo')->isNotFound());
    }

    /**
     * @group new_versions
     */
    public function testOrRedirectCallback()
    {
        (new GeoRoute($this->route, ['uk'], 'allow'))->orRedirectTo('grault');

        $this->router->get('/quux', ['uses' => 'CorgeController@uier', 'as' => 'grault']);

        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn(json_decode('{"countryCode": "fr"}'));

        $response = $this->get('/foo');
        $url = $this->app->make('url');

        $this->assertTrue($response->isRedirect());
        $this->assertEquals($url->route('grault'), $url->to($response->headers->get('Location')));
    }
}
