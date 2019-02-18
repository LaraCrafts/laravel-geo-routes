<?php

namespace LaraCrafts\GeoRoutes;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use LaraCrafts\GeoRoutes\Http\Middleware\GeoMiddleware;
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
        $this->mergeConfigFrom(__DIR__ . '/../config/global.php', 'geo-routes.global');
        $this->mergeConfigFrom(__DIR__ . '/../config/routes.php', 'geo-routes.routes');
        $this->publishes([__DIR__ . '/../config' => config_path('geo-routes')], 'config');

        if (version_compare($this->app->version(), '5.5.0', '>=')) {
            $this->registerMacros();
        }

        $this->registerGlobalMiddleware();
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $router = $this->app->make('router');

        if (method_exists($router, 'aliasMiddleware')) {
            $router->aliasMiddleware('geo', GeoRoutesMiddleware::class);
        } else {
            $router->middleware('geo', GeoRoutesMiddleware::class);
        }
    }

    /**
     * Register the global middleware.
     *
     * @return void
     */
    protected function registerGlobalMiddleware()
    {
        if (!$this->app['config']['geo-routes.global.enabled']) {
            return;
        }

        $this->app->make(Kernel::class)->pushMiddleware(GeoMiddleware::class);
    }

    /**
     * Register the route macros.
     *
     * @return void
     */
    protected function registerMacros()
    {
        Route::macro('allowFrom', function (string ...$countries) {
            return new GeoRoute($this, $countries, true);
        });

        Route::macro('denyFrom', function (string ...$countries) {
            return new GeoRoute($this, $countries, false);
        });

        Router::macro('geogroup', function (array $attributes, callable $routes) {
            return new GeoGroup($attributes, $routes);
        });
    }
}
