<?php

namespace LaraCrafts\GeoRoutes\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaraCrafts\GeoRoutes\DeterminesGeoAccess;

class GeoMiddleware
{
    use DeterminesGeoAccess;

    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->shouldHaveAccess($request)) {
            abort(401);
        }

        return $next($request);
    }

    /**
     * Get the countries.
     *
     * @return string[]
     */
    public function getCountries()
    {
        return config('geo-routes.global.countries');
    }

    /**
     * Get the strategy.
     *
     * @return string
     */
    public function getStrategy()
    {
        return config('geo-routes.global.strategy');
    }
}
