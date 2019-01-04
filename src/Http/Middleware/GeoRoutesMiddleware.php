<?php

namespace LaraCrafts\GeoRoutes\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GeoRoutesMiddleware
{
    public function handle(Request $request, Closure $next, string $rule, string $countries)
    {
        $countries = explode('&', $countries);

        if ($this->shouldHaveAccess($request, $countries, $rule)) {
            return $next($request);
        }

        # TODO: implement callbacks here
        return abort(401);
    }

    protected function shouldHaveAccess(Request $request, array $countries, string $strategy)
    {
        $requestCountry = $request->query('country', 'us'); # TODO: replace this with a geo ip provider call

        if ($strategy === 'allow') {
            return in_array($requestCountry, $countries);
        }

        return !in_array($requestCountry, $countries);
    }
}
