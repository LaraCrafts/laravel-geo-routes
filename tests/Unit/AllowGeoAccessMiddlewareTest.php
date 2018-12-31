<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use Illuminate\Http\Request;
use LaraCrafts\GeoRoutes\Http\Middleware\AllowGeoAccess;
use LaraCrafts\GeoRoutes\Tests\TestCase;

class AllowGeoAccessMiddlewareTest extends TestCase
{
    protected $request;

    protected $middleware;

    protected function setUp()
    {
        parent::setUp();

        $this->request = new request();
        $this->middleware = new AllowGeoAccess();
    }

    /**
     * @test
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function fake_location_access_is_denied()
    {
        $this->middleware->handle($this->request, function ($request) {}, 'fake-location');
    }

    /** @test */
    public function the_default_location_works()
    {
        $response = $this->middleware->handle($this->request, function ($request) {}, 'us');

        $this->assertNull($response);
    }
}
