<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use Illuminate\Http\Request;
use LaraCrafts\GeoRoutes\Http\Middleware\GeoRoutesMiddleware;
use LaraCrafts\GeoRoutes\Tests\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GeoRoutesMiddlewareTest extends TestCase
{
    /** @var \LaraCrafts\GeoRoutes\Http\Middleware\GeoRoutesMiddleware */
    protected $middleware;

    /** @var \Closure */
    protected $next;

    /** @var \Illuminate\Http\Request */
    protected $request;

    protected function setUp()
    {
        parent::setUp();

        $this->middleware = new GeoRoutesMiddleware();
        $this->next = function () {
            return 'User got through';
        };
        $this->request = new Request(['country' => 'us']);
    }

    public function testIfAccessIsDenied()
    {
        $this->expectException(HttpException::class);
        $this->middleware->handle($this->request, $this->next, 'deny', 'us');
    }

    public function testIfAccessIsAllowed()
    {
        $output = $this->middleware->handle($this->request, $this->next, 'allow', 'us');
        $this->assertEquals('User got through', $output);
    }
}
