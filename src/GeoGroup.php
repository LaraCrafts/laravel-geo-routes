<?php

namespace LaraCrafts\GeoRoutes;

class GeoGroup
{
    use CallbacksHandler;

    /**
     * The route group attributes
     *
     * @var array
     */
    protected $attributes;

    /**
     * The raw routes
     *
     * @var callable
     */
    protected $routes;

    /**
     * The geo-constraint
     *
     * @var GeoConstraint
     */
    protected $constraint;

    /**
     * The countries covered by the constraint
     *
     * @var array
     */
    protected $countries;

    /**
     * Create a new GeoGroup instance
     *
     * @param array $attributes
     * @param callable $routes
     */
    public function __construct(array $attributes, callable $routes)
    {
        $this->attributes = $attributes;
        $this->routes = $routes;

        $this->constraint = new GeoConstraint(false, []);

        CallbacksRegistrar::init();
    }

    /**
     * Destruct the GeoGroup instance
     */
    public function __destruct()
    {
        $this->constraint->setCountries(...$this->countries ?? []);

        $this->attributes = array_merge($this->attributes, [
                                            'GeoConstraint' => $this->constraint,
                                            'middleware' => 'geo',
                                        ]);

        app('router')->group($this->attributes, $this->routes);
    }

    /**
     * Allow access from given countries
     *
     * @param string ...$countries
     *
     * @return $this
     */
    public function allowFrom(string ...$countries)
    {
        $this->countries = $countries;
        $this->constraint->setAccess(true);

        return $this;
    }

    /**
     * Deny access from given countries
     *
     * @param string ...$countries
     *
     * @return $this
     */
    public function denyFrom(string ...$countries)
    {
        $this->countries = $countries;
        $this->constraint->setAccess(false);

        return $this;
    }
}
