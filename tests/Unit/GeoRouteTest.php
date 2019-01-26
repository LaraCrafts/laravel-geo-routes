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

    /**
     * @group global
     */
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
     * @group 5.1-5.2
     */
    public function testDefaultCallback_51_52()
    {
        $exceptionThrown = false;

        (new GeoRoute($this->route, ['kr'], 'allow'));

        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn((object) ['countryCode' => 'ca']);

        try {
            $response = $this->get('/foo');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(401, $e->getStatusCode());
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    /**
     * @group 5.1-5.2
     */
    public function testOrNotFoundCallback_51_52()
    {
        $exceptionThrown = false;

        (new GeoRoute($this->route, ['gb'], 'allow'))->orNotFound();

        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn((object) ['countryCode' => 'us']);

        try {
            $response = $this->get('/foo');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown);
    }

    /**
     * @group 5.1-5.2
     */
    public function testOrRedirectCallback_52()
    {
        (new GeoRoute($this->route, ['uk'], 'allow'))->orRedirectTo('grault');

        $this->router->get('/quux', ['uses' => 'CorgeController@uier', 'as' => 'grault']);

        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn((object) ['countryCode' => 'fr']);

        $response = $this->get('/foo');

        $response->assertRedirectedToRoute('grault');
    }

    /**
     * @group 5.3
     */
    public function testDefaultCallback_53()
    {
        (new GeoRoute($this->route, ['kr'], 'allow'));

        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn((object) ['countryCode' => 'ca']);

        $response = $this->get('/foo');

        $response->assertResponseStatus(401);
    }

    /**
     * @group 5.3
     */
    public function testOrNotFoundCallback_53()
    {
        (new GeoRoute($this->route, ['gb'], 'allow'))->orNotFound();

        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn((object) ['countryCode' => 'us']);


        $response = $this->get('/foo');

        $response->assertResponseStatus(404);
    }

    /**
     * @group 5.3
     */
    public function testOrRedirectCallback_53()
    {
        (new GeoRoute($this->route, ['uk'], 'allow'))->orRedirectTo('grault');

        $this->router->get('/quux', ['uses' => 'CorgeController@uier', 'as' => 'grault']);

        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn((object) ['countryCode' => 'fr']);

        $response = $this->get('/foo');

        $response->assertRedirectedToRoute('grault');
    }

    /**
     * @group 5.4-5.7
     */
    public function testDefaultCallback_54up()
    {
        (new GeoRoute($this->route, ['kr'], 'allow'));

        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn((object) ['countryCode' => 'ca']);

        $response = $this->get('/foo');

        $response->assertStatus(401);
    }

    /**
     * @group 5.4-5.7
     */
    public function testOrNotFoundCallback_54up()
    {
        $exceptionThrown = false;

        (new GeoRoute($this->route, ['gb'], 'allow'))->orNotFound();

        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn((object) ['countryCode' => 'us']);

        $response = $this->get('/foo');

        $response->assertStatus(404);
    }

    /**
     * @group 5.4-5.7
     */
    public function testOrRedirectCallback_54up()
    {
        (new GeoRoute($this->route, ['uk'], 'allow'))->orRedirectTo('grault');

        $this->router->get('/quux', ['uses' => 'CorgeController@uier', 'as' => 'grault']);

        $this->location
            ->shouldReceive('get')
            ->once()
            ->andReturn((object) ['countryCode' => 'fr']);

        $response = $this->get('/foo');

        $response->assertRedirect('/quux');
    }
}
