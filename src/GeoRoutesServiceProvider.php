<?php

namespace LaraCrafts\GeoRoutes;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use LaraCrafts\GeoRoutes\Http\Middleware\GeoRoutesMiddleware;

class GeoRoutesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        Route::macro('allowFrom', function (string ...$countries) {
            return new GeoRoutes($this, $countries, 'allow');
        });

        Route::macro('denyFrom', function (string ...$countries) {
            return new GeoRoutes($this, $countries, 'deny');
        });

        Route::macro('from', function (string ...$countries) {
            return new GeoRoutes($this, $countries, 'allow');
        });
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->extend('router', function (Router $router) {
            return $router->aliasMiddleware('geo', GeoRoutesMiddleware::class);
        });
    }
}
