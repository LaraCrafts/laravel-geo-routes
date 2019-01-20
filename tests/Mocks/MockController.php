<?php

namespace LaraCrafts\GeoRoutes\Tests\Mocks;

use Illuminate\Routing\Controller;

class MockController extends Controller
{
    public function index()
    {
        return response('Hello world!', 200);
    }
}
