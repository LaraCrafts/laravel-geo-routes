<?php

namespace LaraCrafts\GeoRoutes\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GeoRoutesMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $strategy
     * @param string $countries
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $strategy, string $countries)
    {
        $countries = explode('&', $countries);

        if ($this->shouldHaveAccess($request, $countries, $strategy)) {
            return $next($request);
        }

        # TODO: implement callbacks here
        return abort(401);
    }

    /**
     * Determine if the request should be allowed through.
     *
     * @param \Illuminate\Http\Request $request
     * @param array $countries
     * @param string $strategy
     * @return bool
     */
    protected function shouldHaveAccess(Request $request, array $countries, string $strategy)
    {
        $requestCountry = $request->query('country', 'us'); # TODO: replace this with a geo ip provider call

        if ($strategy === 'allow') {
            return in_array($requestCountry, $countries);
        }

        return !in_array($requestCountry, $countries);
    }
}
