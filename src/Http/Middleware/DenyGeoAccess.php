<?php

namespace LaraCrafts\GeoRoutes\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DenyGeoAccess
{
    public function handle(Request $request, Closure $next, string ...$countries)
    {
        # TODO: implement GeoIP
        $countries = array_map('strtolower', $countries);
        $country = strtolower($request->query('country', 'us'));

        if (in_array($country, $countries)) {
            # TODO: implement deny access strategy
            return abort(404);
        }

        return $next($request);
    }
}
