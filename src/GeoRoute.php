<?php

namespace LaraCrafts\GeoRoutes;

use Illuminate\Routing\Route;
use InvalidArgumentException;

/**
 * @mixin \Illuminate\Routing\Route
 */
class GeoRoute
{
    use CallbacksHandler;

    /**
     * Rule is applied.
     *
     * @var bool
     */
    protected $applied;

    /**
     * The callback to execute if the visitor
     * is not allowed.
     *
     * @var array
     */
    protected $callback;

    /**
     * The countries to apply the rule for.
     *
     * @var array
     */
    protected $countries;

    /**
     * The callbacks' proxies.
     *
     * @var array
     */
    protected static $proxies;

    /**
     * The route.
     *
     * @var \Illuminate\Routing\Route
     */
    protected $route;

    /**
     * Determine if the requests are
     * allowed from the given countries
     *
     * @var boolean
     */
    protected $allowed;

    /**
     * The geo-constraint
     *
     * @var GeoConstraint
     */
    protected $constraint;

    /**
     * Create a new GeoRoute instance.
     *
     * @param \Illuminate\Routing\Route $route
     * @param array $countries
     * @param string $allowed
     * @throws \InvalidArgumentException
     */
    public function __construct(Route $route, array $countries, bool $allowed)
    {
        $this->applied = false;
        $this->countries = array_map('strtoupper', $countries);
        $this->route = $route;
        $this->constraint = new GeoConstraint($allowed, $countries);
        $this->constraint->setAccess($allowed);

        CallbacksRegistrar::init();
    }

    /**
     * Destruct the GeoRoute instance and apply the middleware.
     */
    public function __destruct()
    {
        $this->applyConstraint();
    }

    /**
     * Apply the GeoConstraint
     *
     * @return void
     */
    protected function applyConstraint()
    {
        if ($this->applied || !$this->countries) {
            return;
        }

        $action = $this->route->getAction();
        $action['middleware'][] = 'geo';

        $this->applied = true;
        $this->route->setAction($action);
        $this->route->GeoConstraint = $this->constraint;
    }
}
