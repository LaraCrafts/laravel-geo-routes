<?php

namespace LaraCrafts\GeoRoutes;

use BadMethodCallException;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

/**
 * @mixin \Illuminate\Routing\Route
 */
class GeoRoute
{
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
     * Determines if the access is allowed to
     * the given countries.
     *
     * @var boolean
     */
    protected $allowed;

    /**
     * Create a new GeoRoute instance.
     *
     * @param \Illuminate\Routing\Route $route
     * @param array $countries
     * @param boolean $allowed
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Route $route, array $countries, bool $allowed)
    {
        $this->applied = false;
        $this->countries = array_map('strtoupper', $countries);
        $this->route = $route;
        $this->allowed = $allowed;

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

        if (array_key_exists($method, static::$proxies)) {
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
        $this->allowed = true;

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
            'allowed' => $this->allowed,
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
        $this->allowed = false;

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

        static::$proxies = [];
        $callbacks = config('geo-routes.routes.callbacks');

        foreach ($callbacks as $key => $callback) {
            static::$proxies['or' . Str::studly($key)] = $callback;
        }
    }

    /**
     * Return a HTTP 404 error if access is denied.
     *
     * @return $this
     */
    public function orNotFound()
    {
        return $this->setCallback('LaraCrafts\GeoRoutes\Callbacks::notFound', func_get_args());
    }

    /**
     * Redirect to given route if access is denied.
     *
     * @param string $routeName
     *
     * @return $this
     */
    public function orRedirectTo(string $routeName)
    {
        return $this->setCallback('LaraCrafts\GeoRoutes\Callbacks::redirectTo', func_get_args());
    }

    /**
     * Return a HTTP 401 error if access is denied (this is the default behavior).
     *
     * @return $this
     */
    public function orUnauthorized()
    {
        $this->callback = null;

        return $this;
    }

    /**
     * Set the callback.
     *
     * @param callable $callback
     * @param array $arguments
     *
     * @return $this
     */
    protected function setCallback(callable $callback, array $arguments)
    {
        if (is_string($callback) && Str::contains($callback, '@')) {
            $callback = Str::parseCallback($callback, '__invoke');
            $callback[0] = resolve($callback[0]);
        }

        $this->callback = [$callback, $arguments];

        return $this;
    }
}
