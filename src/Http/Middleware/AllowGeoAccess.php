<?php

namespace LaraCrafts\GeoRoutes\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowGeoAccess
{
    public function handle(Request $request, Closure $next, string ...$countries)
    {
        # TODO: implement GeoIP
        $countries = array_map('strtolower', $countries);
        $country = strtolower($request->query('country', 'us'));

        if (in_array($country, $countries)) {
            return $next($request);
        }

        # TODO: implement deny access strategy
        return abort(404);
    }
}
