<?php

namespace LaraCrafts\GeoRoutes\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaraCrafts\GeoRoutes\CallbacksRegistrar;
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
        $constraint = $request->route()->GeoConstraint ?? $request->route()->action['GeoConstraint'];

        $countries = $constraint->getCountries();
        $callback = $constraint->getCallback();

        if ($this->shouldHaveAccess($request, $countries, $constraint->isAllowed())) {
            return $next($request);
        }

        if (!is_null($callback)) {
            return $callback();
        }

        return abort(401);
    }
}
