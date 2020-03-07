<?php

namespace LaraCrafts\GeoRoutes\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void addCallback(string $name, callable $callback)
 * @method static void loadCallbacks(array $callbacks)
 * @method static array|null callbacks(array $callbacks = null)
 * @method static void parseCallbacks(string $class)
 * @method static mixed callback(string $name, callable $callable = null)
 * @method static boolean hasCallback(string $name)
 * @method static boolean hasProxy(string $proxy)
 * @method static void setDefault(string|callable $callback, ...$arguments)
 * @method static array getDefault()
 * @method static mixed invokeDefaultCallback()
 *
 * @see \LaraCrafts\GeoRoutes\CallbackRegistrar
 */
class CallbackRegistrar extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'georoutes.callbacks';
    }
}
