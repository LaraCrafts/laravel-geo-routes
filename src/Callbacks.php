<?php

namespace LaraCrafts\GeoRoutes;

use Symfony\Component\HttpKernel\Exception\HttpException;

class Callbacks
{
    /**
     * Return a HTTP 404 Not Found response.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public static function notFound()
    {
        throw new HttpException(404);
    }

    /**
     * Redirect to given route.
     *
     * @param string $route
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public static function redirectTo(string $route)
    {
        return redirect()->route($route);
    }
}
