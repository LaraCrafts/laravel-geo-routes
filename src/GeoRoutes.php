<?php

namespace LaraCrafts\GeoRoutes;

use BadMethodCallException;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

/**
 * @mixin \Illuminate\Routing\Route
 */
class GeoRoutes
{
    protected $applied;
    protected $callback;
    protected $countries;
    protected static $proxies;
    protected $route;
    protected $strategy;

    /**
     * Create a new GeoRoutes instance.
     *
     * @param \Illuminate\Routing\Route $route
     * @param array $countries
     * @param string $strategy
     * @return void
     */
    public function __construct(Route $route, array $countries, string $strategy)
    {
        $this->applied = false;
        $this->countries = $countries;
        $this->route = $route;
        $this->strategy = $strategy;

        static::loadProxies();
    }

    /**
     * Dynamically call the underlying route.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        if (method_exists($this->route, $method)) {
            return $this->route->$method(...$arguments);
        }

        if (array_key_exists($method, static::$proxies)) {
            return $this->setCallback($method, $arguments);
        }

        throw new BadMethodCallException("Undefined method '$method'");
    }

    /**
     * Destruct the GeoRoutes instance and apply the middleware
     */
    public function __destruct()
    {
        $this->applyMiddleware();
    }

    /**
     * Generate a middleware string
     *
     * @return string
     */
    public function __toString()
    {
        return 'geo:' . $this->strategy . ',' . implode('&', $this->countries) .
            ($this->callback ? ',' . serialize($this->callback) : '');
    }

    /**
     * Allow given countries.
     *
     * @return $this
     */
    public function allow()
    {
        $this->strategy = 'allow';
        return $this;
    }

    /**
     * Apply the middleware to the route.
     *
     * @return void
     */
    protected function applyMiddleware()
    {
        if ($this->applied) {
            return;
        }

        $this->applied = true;
        $this->route->middleware((string)$this);
    }

    /**
     * Deny given countries.
     *
     * @return $this
     */
    public function deny()
    {
        $this->strategy = 'deny';
        return $this;
    }

    /**
     * Load the available proxies.
     */
    protected static function loadProxies()
    {
        if (static::$proxies !== null) {
            return;
        }

        // We explicitly assign it as an empty array here, so that even if there are no registered callbacks we still
        // won't keep reading config
        static::$proxies = [];

        foreach (config('geo-routes.callbacks') as $key => $callback) {
            static::$proxies['or' . Str::studly($key)] = $callback;
        }
    }

    /**
     * Set the callback.
     *
     * @param string $proxy
     * @param array $arguments
     * @return $this
     */
    protected function setCallback(string $proxy, array $arguments)
    {
        $callback = static::$proxies[$proxy];

        if (!is_callable($callback)) {
            $callback = Str::parseCallback($callback, '__invoke');
            $callback[0] = resolve($callback[0]);
        }

        $this->callback = [$callback, $arguments];
        return $this;
    }
}
