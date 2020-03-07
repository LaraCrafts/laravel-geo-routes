<?php

namespace LaraCrafts\GeoRoutes\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaraCrafts\GeoRoutes\DeterminesGeoAccess;
use LaraCrafts\GeoRoutes\Support\Facades\CallbackRegistrar;

class GeoMiddleware
{
    use DeterminesGeoAccess;

    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $countries = config()->get('geo-routes.global.countries');
        $strategy = config()->get('geo-routes.global.strategy');

        if (!$this->shouldHaveAccess($countries, $strategy)) {
            return CallbackRegistrar::invokeDefaultCallback();
        }

        return $next($request);
    }
}
