<?php

namespace LaraCrafts\GeoRoutes;

use BadMethodCallException;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use LaraCrafts\GeoRoutes\Support\Facades\CallbackRegistrar;

/**
 * @mixin \Illuminate\Routing\Route
 */
class GeoRoute
{
    use Concerns\HasCallback;
    use Concerns\ControlsAccess;

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
     * @param  \Illuminate\Routing\Route $route
     * @param  array $countries
     * @param  string $strategy
     */
    public function __construct(Route $route, array $countries, string $strategy)
    {
        $this->applied = false;
        $this->countries = array_map('strtoupper', $countries);
        $this->route = $route;
        $this->strategy = $strategy;
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

        if (CallbackRegistrar::hasProxy($method)) {
            return $this->setCallback(CallbackRegistrar::callback($method), $arguments);
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
}
