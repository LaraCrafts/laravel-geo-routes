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
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();

        if (!$route) {
            #TODO: Invoke the default callback.
            return abort(401);
        }

        $geo = $route->getAction('geo');

        if ($this->shouldHaveAccess($request, (array)$geo['countries'], $geo['strategy'])) {
            return $next($request);
        }

        if (array_key_exists('callback', $geo) && $callback = $geo['callback']) {
            return call_user_func_array($callback[0], $callback[1]);
        }

        return abort(401);
    }
}
