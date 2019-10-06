<?php

namespace LaraCrafts\GeoRoutes;

use Closure;
use Illuminate\Support\Arr;

class GeoGroup
{
    use Concerns\HasCallback;
    use Concerns\ControlsAccess;

    /**
     * Determines if the geo rule is applied.
     *
     * @var bool
     */
    protected $applied;

    /**
     * The routes closure.
     *
     * @var \Closure
     */
    protected $routes;

    /**
     * The routes group shared attributes.
     *
     * @var array
     */
    protected $attributes;

    /**
     * The router instance.
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
    * The attributes that can be set through this class.
    *
    * @var array
    */
    protected $allowedAttributes = [
       'as', 'domain', 'middleware', 'name', 'namespace', 'prefix', 'where',
   ];

    /**
     * The attributes that are aliased.
     *
     * @var array
     */
    protected $aliases = [
       'name' => 'as',
   ];

    /**
     * Create a new GeoGroup instance.
     *
     * @param array $attributes
     * @param \Closure $routes
     */
    public function __construct(array $attributes, Closure $routes)
    {
        $this->attributes = $attributes;
        $this->routes = $routes;
        $this->router = app('router');
        $this->applied = false;

        static::loadProxies();
    }

    /**
     * Destruct the GeoGroup instance and apply the rule.
     */
    public function __destruct()
    {
        $this->applyConstraint();
    }

    /**
     * Set the array of countries covered by the rule.
     *
     * @param string ...$countries
     *
     * @return $this
     */
    public function from(string ...$countries)
    {
        $this->countries = $countries;

        return $this;
    }

    /**
     * Apply the geo-constraint to the routes group.
     */
    protected function applyConstraint()
    {
        if ($this->applied || !$this->countries) {
            return;
        }

        $attributes = $this->attributes;
        $attributes['middleware'][] = 'geo';
        $attributes['geo'] = [
            'strategy' => $this->strategy,
            'countries' => (array)$this->countries,
            'callback' => $this->callback,
        ];

        $this->router->group($attributes, $this->routes);

        $this->applied = true;
    }

    /**
     * Dynamically call the router methods.
     *
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        if ($this->callbackExists($method)) {
            return $this->setCallback(static::$proxies[$method], $arguments);
        }

        if ($this->router::hasMacro($method)) {
            return $this->macroCall($method, $arguments);
        }

        if ($method === 'middleware') {
            return $this->attribute($method, is_array($arguments[0]) ? $arguments[0] : $arguments);
        }

        return ($this)->attribute($method, $arguments[0]);
    }

    /**
     * Set the value for a given attribute.
     *
     * @param  string  $key
     * @param  mixed  $value
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function attribute($key, $value)
    {
        if (! in_array($key, $this->allowedAttributes)) {
            throw new \InvalidArgumentException("Attribute [{$key}] does not exist.");
        }

        $this->attributes[Arr::get($this->aliases, $key, $key)] = $value;

        return $this;
    }
}
