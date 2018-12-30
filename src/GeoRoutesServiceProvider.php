<?php

namespace LaraCrafts\GeoRoutes;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use LaraCrafts\GeoRoutes\Http\Middleware\AllowGeoAccess;
use LaraCrafts\GeoRoutes\Http\Middleware\DenyGeoAccess;

class GeoRoutesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Route::macro('allow', function (string ...$countries) {
            return $this->middleware('allow:' . implode(',', $countries));
        });

        Route::macro('deny', function (string ...$countries) {
            return $this->middleware('deny:' . implode(',', $countries));
        });

        $this->app->extend('router', function (Router $router) {
            return $router
                ->aliasMiddleware('allow', AllowGeoAccess::class)
                ->aliasMiddleware('deny', DenyGeoAccess::class);
        });
    }
}
