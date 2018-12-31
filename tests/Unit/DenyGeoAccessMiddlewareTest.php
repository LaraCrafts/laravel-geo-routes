<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use Illuminate\Http\Request;
use LaraCrafts\GeoRoutes\Http\Middleware\DenyGeoAccess;
use LaraCrafts\GeoRoutes\Tests\TestCase;

class DenyGeoAccessMiddlewareTest extends TestCase
{
    protected $request;

    protected $middleware;

    protected function setUp()
    {
        parent::setUp();

        $this->request = new request();
        $this->middleware = new DenyGeoAccess();
    }

    /**
     * @test
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function default_location_is_denied_access()
    {
        $this->middleware->handle($this->request, function ($request) {}, 'us');
    }

    /** @test */
    public function any_location_except_default_is_allowed()
    {
        $response = $this->middleware->handle($this->request, function ($request) {}, 'fake-location');

        $this->assertNull($response);
    }
}
