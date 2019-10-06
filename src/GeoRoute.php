<?php

namespace LaraCrafts\GeoRoutes;

use BadMethodCallException;
use Illuminate\Routing\Route;

/**
 * @mixin \Illuminate\Routing\Route
 */
class GeoRoute
{
    use Concerns\HasCallback;

    /**
     * Rule is applied.
     *
     * @var bool
     */
    protected $applied;

    /**
     * The countries to apply the rule for.
     *
     * @var array
     */
    protected $countries;

    /**
     * The route.
     *
     * @var \Illuminate\Routing\Route
     */
    protected $route;

    /**
     * The rule's strategy.
     *
     * @var string
     */
    protected $strategy;

    /**
     * Create a new GeoRoute instance.
     *
     * @param \Illuminate\Routing\Route $route
     * @param array $countries
     * @param string $strategy
     * @throws \InvalidArgumentException
     */
    public function __construct(Route $route, array $countries, string $strategy)
    {
        $this->applied = false;
        $this->countries = array_map('strtoupper', $countries);
        $this->route = $route;
        $this->strategy = $strategy;

        static::loadProxies();
    }

    /**
     * Dynamically call the underlying route.
     *
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        if (method_exists($this->route, $method) || Route::hasMacro($method)) {
            return $this->route->$method(...$arguments);
        }

        if ($this->callbackExists($method)) {
            return $this->setCallback(static::$proxies[$method], $arguments);
        }

        throw new BadMethodCallException("Undefined method '$method'");
    }

    /**
     * Destruct the GeoRoute instance and apply the middleware.
     */
    public function __destruct()
    {
        $this->applyConstraint();
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
     * Apply the geo-constraint to the route.
     */
    protected function applyConstraint()
    {
        if ($this->applied || !$this->countries) {
            return;
        }

        $action = $this->route->getAction();
        $action['middleware'][] = 'geo';
        $action['geo'] = [
            'strategy' => $this->strategy,
            'countries' => (array)$this->countries,
            'callback' => $this->callback,
        ];

        $this->route->setAction($action);

        $this->applied = true;
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
