<?php

namespace LaraCrafts\GeoRoutes\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use LaraCrafts\GeoRoutes\DeterminesGeoAccess;
use LaraCrafts\GeoRoutes\Support\Facades\CallbackRegistrar;

class GeoRoutesMiddleware
{
    use DeterminesGeoAccess;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();

        if (!$route) {
            return CallbackRegistrar::invokeDefaultCallback();
        }

        $constraint = $route->getAction('geo') ?? [];

        $validator = Validator::make($constraint, [
            'countries' => 'required|array|min:1',
            'countries.*' => 'string|min:2|max:2',
            'strategy' => 'required|in:allow,deny',
        ]);

        if ($validator->fails()) {
            throw new Exception("The GeoRoute constraint is invalid.");
        }

        if ($this->shouldHaveAccess((array)$constraint['countries'], $constraint['strategy'])) {
            return $next($request);
        }

        if (array_key_exists('callback', $constraint) && $callback = $constraint['callback']) {
            return call_user_func_array($callback[0], $callback[1]);
        }

        return CallbackRegistrar::invokeDefaultCallback();
    }
}
