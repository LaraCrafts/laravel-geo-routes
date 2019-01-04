<?php

namespace LaraCrafts\GeoRoutes;

use BadMethodCallException;
use Illuminate\Routing\Route;

/**
 * @mixin \Illuminate\Routing\Route
 */
class GeoRoutes
{
    protected $applied;
    protected $countries;
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
        return 'geo:' . $this->strategy . ',' . implode('&', $this->countries);
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
}
