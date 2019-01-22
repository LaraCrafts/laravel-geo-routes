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
     * @throws \InvalidArgumentException
     */
    public function __construct(Route $route, array $countries)
    {
        $this->applied = false;
        $this->countries = array_map('strtoupper', $countries);
        $this->route = $route;

        static::loadProxies();
        $this->loadDefaultSettings();
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
        if (method_exists($this->route, $method)) {
            return $this->route->$method(...$arguments);
        }

        if ($this->countries == []) {
            return $this->processMacroReplacements($method, $arguments);
        }

        if (array_key_exists($method, static::$proxies)) {
            return $this->setCallback(static::$proxies[$method], $arguments);
        }

        throw new BadMethodCallException("Undefined method '$method'");
    }

    /**
     * Process the macro methods' replacements
     *
     * @param string $method
     * @param array $args
     * @return $this
     */
    protected function processMacroReplacements(string $method, array $args)
    {
        $this->countries = array_map('strtoupper', $args);

        if ($method == 'allowFrom') {
            return $this->allow();
        }

        if ($method == 'denyFrom') {
            return $this->deny();
        }

        return $this;
    }

    /**
     * Destruct the GeoRoute instance and apply the middleware.
     */
    public function __destruct()
    {
        $this->applyMiddleware();
    }

    /**
     * Generate a middleware string.
     *
     * @return string
     */
    public function __toString()
    {
        return 'geo:' . $this->strategy . ',' . implode('&', $this->countries) .
            ($this->callback ? ',' . serialize($this->callback) : '');
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
     */
    protected function applyMiddleware()
    {
        if ($this->applied || !$this->countries) {
            return;
        }

        $action = $this->route->getAction();
        $action['middleware'] = (string)$this;

        $this->applied = true;
        $this->route->setAction($action);
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

    /**
     * Load the available proxies.
     */
    protected static function loadProxies()
    {
        if (static::$proxies !== null) {
            return;
        }

        static::$proxies = [];
        $defaultCallbacksClassName = Callbacks::class;

        $callbacks = config('geo-routes.callbacks');

        $defaultCallbacksReflection = new \ReflectionClass($defaultCallbacksClassName);

        $defaultCallbacks = $defaultCallbacksReflection->getMethods(\ReflectionMethod::IS_STATIC);

        //Load default callbacks
        foreach ($defaultCallbacks as $callback) {
            static::$proxies['or' . Str::studly($callback->name)] = $defaultCallbacksClassName . '::' . $callback->name;
        }

        //Load custom callbacks
        foreach ($callbacks as $key => $callback) {
            static::$proxies['or' . Str::studly($key)] = $callback;
        }

        static::$proxies['orUnauthorized'] = null;
    }

    /**
     * Load the default settings
     *
     * @return void
     */
    protected function loadDefaultSettings()
    {
        $defaults = config('geo-routes.defaults');

        $this->strategy = $defaults['RULE'];
        $defaultCallback = $defaults['CALLBACK'];

        if (is_null(static::$proxies[$defaultCallback['name']])) {
            $this->callback = null;
            return;
        }

        $this->setCallback(static::$proxies[$defaultCallback['name']], $defaultCallback['args'] ?? []);
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
