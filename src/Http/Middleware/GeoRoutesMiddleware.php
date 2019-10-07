<?php

namespace LaraCrafts\GeoRoutes\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use LaraCrafts\GeoRoutes\DeterminesGeoAccess;

class GeoRoutesMiddleware
{
    use DeterminesGeoAccess;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $allowed
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

        $constraint = $route->getAction('geo') ?? [];

        $validator = Validator::make($constraint, [
            'countries' => 'required|array|min:1',
            'countries.*' => 'string|min:2|max:2',
            'allowed' => 'required',
        ]);

        if ($validator->fails()) {
            throw new Exception("The GeoRoute constraint is invalid.");
        }

        if ($this->shouldHaveAccess((array)$constraint['countries'], $constraint['allowed'])) {
            return $next($request);
        }

        if (array_key_exists('callback', $constraint) && $callback = $constraint['callback']) {
            return call_user_func_array($callback[0], $callback[1]);
        }

        return abort(401);
    }
}
