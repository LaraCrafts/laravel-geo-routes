<?php

namespace LaraCrafts\GeoRoutes;

use BadMethodCallException;
use Illuminate\Routing\Route;

class GeoRoutes
{
    protected $applied;
    protected $countries;
    protected $route;
    protected $strategy;

    public function __construct(Route $route, array $countries, string $strategy)
    {
        $this->applied = false;
        $this->countries = $countries;
        $this->route = $route;
        $this->strategy = $strategy;
    }

    public function __call(string $method, array $arguments)
    {
        if (method_exists($this->route, $method)) {
            return $this->route->$method(...$arguments);
        }

        throw new BadMethodCallException("Undefined method '$method'");
    }

    public function __destruct()
    {
        $this->applyMiddleware();
    }

    public function __toString()
    {
        return 'geo:' . $this->strategy . ',' . implode('&', $this->countries);
    }

    public function allow()
    {
        $this->strategy = 'allow';
        return $this;
    }

    protected function applyMiddleware()
    {
        if ($this->applied) {
            return;
        }

        $this->applied = true;
        $this->route->middleware((string)$this);
    }

    public function deny()
    {
        $this->strategy = 'deny';
        return $this;
    }
}
