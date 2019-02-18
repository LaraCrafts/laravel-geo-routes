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

	public function __construct(array $attributes, callable $routes)
	{
        $this->attributes = $attributes;
        $this->routes = $routes;

        $this->constraint = new GeoConstraint(true, []);

        CallbacksRegistrar::init();
    }

    public function __destruct()
    {
        $this->attributes = array_merge($this->attributes, [
                                            'GeoConstraint' => $this->constraint,
                                            'middleware' => 'geo'
                                        ]);

        app('router')->group($this->attributes, $this->routes);
    }

    public function allowFrom(string ...$countries)
    {
        $this->countries = $countries;
        $this->constraint->setAccess(true);

        return $this;
    }

    public function denyFrom(string ...$countries)
    {
        $this->countries = $countries;
        $this->constraint->setAccess(false);

        return $this;
    }

}
