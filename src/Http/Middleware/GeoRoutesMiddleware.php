<?php

namespace LaraCrafts\GeoRoutes\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaraCrafts\GeoRoutes\DeterminesGeoAccess;

class GeoRoutesMiddleware
{
    use DeterminesGeoAccess;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $strategy
     * @param string $countries
     * @param string|null $callback
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
}
