<?php

namespace LaraCrafts\GeoRoutes;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CallbacksRegistrar
{
    /**
     * The callbacks registrar
     *
     * @var array
     */
    protected static $registrar;

    /**
     * Callbacks-Constraints bindings
     *
     * @var array
     */
    protected static $bindings;

    /**
     * Determines whether the built-in callbacks
     * are loaded
     *
     * @var boolean
     */
    protected static $loadedDefaultCallbacks= false;

    /**
     * Determines whether the custom callbacks
     * are loaded
     *
     * @var boolean
     */
    protected static $loadedCustomCallbacks = false;

    /**
     * Load all of the default callbacks
     *
     * @return void
     */
    public static function init()
    {
        if (!static::$loadedDefaultCallbacks) {
            self::loadBuiltInCallbacks();
        }

        if (!static::$loadedCustomCallbacks) {
            self::loadCustomCallbacks();
        }
    }

    /**
     * Load built-in default callbacks
     *
     * @return void
     */
    protected static function loadBuiltInCallbacks()
    {
        $reflection = new \ReflectionClass(\LaraCrafts\GeoRoutes\Callbacks::class);

        foreach ($reflection->getMethods() as $method) {
            self::register('or' . Str::studly($method->name), $method->getClosure());
        }

        static::$loadedDefaultCallbacks = true;
    }

    /**
     * Load available callbacks
     *
     * @return void
     */
    protected static function loadCustomCallbacks()
    {
        $callbacks = config('geo-routes.routes.callbacks');

        foreach ($callbacks as $key => $callback) {
            self::register('or' . Str::studly($key), $callback);
        }

        static::$loadedCustomCallbacks = true;
    }

    /**
     * Register a new callback
     *
     * @param string $name
     * @param callable $callback
     *
     * @return void
     */
    public static function register(string $name, callable $callback)
    {
        self::$registrar[$name] = new GeoCallback($name, $callback);
    }

    /**
     * Determine if a callback exists in
     * the registrar
     *
     * @param string $name
     *
     * @return boolean
     */
    public static function has(string $name)
    {
        return Arr::has(self::$registrar, $name);
    }

    /**
     * Get a callback
     *
     * @param string $name
     *
     * @return GeoCallback
     */
    public static function get(string $name)
    {
        if (!self::has($name)) {
            throw new InvalidArgumentException(sprintf('Invalid Callback Name: "%s"', $name));
        }

        return self::$registrar[$name];
    }

    /**
     * Get the callbacks' registrar
     *
     * @return array
     */
    public static function getRegistrar()
    {
        return static::$registrar;
    }

    /**
     * Set the callbacks' registrar
     *
     * @param array $registrar
     *
     * @return void
     */
    public static function setRegistrar(array $registrar)
    {
        return static::$registrar = $registrar;
    }

    /**
     * Determine if a constraint has a callback
     * binding
     *
     * @param string $uniqid
     *
     * @return boolean
     */
    public static function isBound(string $uniqid)
    {
        return Arr::has(static::$bindings, $uniqid);
    }

    /**
     * Bind a callback to a constraint
     *
     * @param string $callback
     * @param array|null $arguments
     *
     * @return string
     */
    public static function bind(string $callback, array $arguments = null)
    {
        $uniqid = uniqid();

        static::$bindings[$uniqid] = self::get($callback)->setArguments($arguments ?? []);

        return $uniqid;
    }

    /**
     * Resolve a callback binding
     *
     * @param string $uniqid
     *
     * @return GeoCallback
     *
     * @throws InvalidArgumentException
     */
    public static function resolve(string $uniqid)
    {
        if (!self::isBound($uniqid)) {
            throw new InvalidArgumentException(sprintf('Invalid Binding Identifier: "%s"', $uniqid));
        }

        return static::$bindings[$uniqid];
    }
}
