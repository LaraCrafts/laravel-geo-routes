<?php

namespace LaraCrafts\GeoRoutes;

use ReflectionClass;
use ReflectionMethod;
use BadMethodCallException;
use Illuminate\Support\Str;
use Illuminate\Routing\Route;
use Symfony\Component\Finder\Finder;
use LaraCrafts\GeoRoutes\Contracts\Callback;
use Illuminate\Contracts\Foundation\Application;

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
     * Determine whether the callbacks proxies
     * are loaded.
     *
     * @var boolean
     */
    protected static $loaded = false;

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
        $action['middleware'][] = (string)$this;

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
     * Load the available callbacks proxies.
     *
     * @return void
     */
    protected static function loadProxies()
    {
        if (static::$loaded) {
            return;
        }

        static::$proxies = [];
        $callbacks = config('geo-routes.routes.callbacks');

        foreach ($callbacks as $key => $callback) {
            static::addCallback($key, $callback);
        }

        static::$loaded = true;
    }

    /**
     * Add a callback proxy from a given name and callable.
     *
     * @param string $name
     * @param callable $callback
     *
     * @return void
     */
    public static function addCallback(string $name, callable $callback)
    {
        static::$proxies['or' . Str::studly($name)] = $callback;
    }

    /**
     * Get the array of callbacks.
     *
     * @return array|null
     */
    public static function callbacks()
    {
        return static::$callbacks;
    }

    /**
     * Parse callbacks from a given class.
     *
     * This method will use reflection to loop through all of the static
     * methods.
     *
     * @param string $class
     *
     * @return void
     */
    public static function parseCallbacks(string $class)
    {
        $reflection = new ReflectionClass($class);
        $callbacks = $reflection->getMethods(ReflectionMethod::IS_STATIC);

        foreach ($callbacks as $callback) {
            static::addCallback($callback->getName(), $callback->getClosure());
        }
    }

    /**
     * Load callbacks from a given path.
     *
     * @param string $path
     *
     * @return void
     */
    public static function loadCallbacksFrom(string $path)
    {
        //
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
