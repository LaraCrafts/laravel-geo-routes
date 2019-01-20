<?php

namespace LaraCrafts\GeoRoutes\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

class GeoRoutesMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string                   $strategy
     * @param string                   $countries
     * @param string|null              $callback
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $strategy, string $countries, string $callback = null)
    {
        $countries = explode('&', $countries);

        if ($this->shouldHaveAccess($request, $countries, $strategy)) {
            return $next($request);
        }

        if ($callback && $callback = unserialize($callback)) {
            return call_user_func_array($callback[0], $callback[1] ?? []);
        }

        return abort(401);
    }

    /**
     * Determine if the request should be allowed through.
     *
     * @param \Illuminate\Http\Request $request
     * @param array                    $countries
     * @param string                   $strategy
     *
     * @return bool
     */
    protected function shouldHaveAccess(Request $request, array $countries, string $strategy)
    {
        $requestCountry = Location::get($request->ip())->countryCode;

        if ($strategy === 'allow') {
            return in_array($requestCountry, $countries);
        }

        return !in_array($requestCountry, $countries);
    }
}
