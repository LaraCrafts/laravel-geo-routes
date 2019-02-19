<?php

namespace LaraCrafts\GeoRoutes;

use Illuminate\Routing\Route;

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
     * Determine if the requests are
     * allowed from the given countries
     *
     * @var boolean
     */
    protected $allowed;

    /**
     * The geo-constraint
     *
     * @var \LaraCrafts\GeoRoutes\GeoConstraint
     */
    protected $constraint;

    /**
     * Create a new GeoRoute instance.
     *
     * @param \Illuminate\Routing\Route $route
     * @param array $countries
     * @param boolean $allowed
     */
    public function __construct(Route $route, array $countries, bool $allowed)
    {
        $this->applied = false;
        $this->countries = $countries;
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
