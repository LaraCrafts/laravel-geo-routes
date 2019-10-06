<?php

namespace LaraCrafts\GeoRoutes;

use Exception;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class CallbacksRegistrar
{
    /**
     * The callbacks' proxies.
     *
     * @var array
     */
    protected $proxies;

    /**
     * Create a new CallbacksRegistrar instance.
     */
    public function __construct(array $callbacks)
    {
        $this->loadProxies($callbacks);
    }

    /**
     * Add a callback proxy from a given name and callable.
     *
     * @param string $name
     * @param callable $callback
     *
     * @return void
     */
    public function addCallback(string $name, callable $callback)
    {
        $this->proxies['or' . Str::studly($name)] = $callback;
    }

    /**
     * Get the callbacks list.
     *
     * @return array|null
     */
    public function callbacks()
    {
        return $this->proxies;
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
    public function parseCallbacks(string $class)
    {
        $reflection = new ReflectionClass($class);
        $callbacks = $reflection->getMethods(ReflectionMethod::IS_STATIC);

        foreach ($callbacks as $callback) {
            $this->addCallback($callback->getName(), $callback->getClosure());
        }
    }

    /**
     * Get/Set the callable for a given callback name.
     *
     * @param string $name
     * @param callable|null $callable
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function callback(string $name, callable $callable = null)
    {
        if (is_callable($callable)) {
            return $this->addCallback($name, $callable);
        }

        if ($this->hasProxy($name)) {
            return $this->proxies[$name];
        }

        if ($this->hasCallback($name)) {
            return $this->proxies['or' . Str::ucfirst($name)];
        }

        throw new Exception("Undefined callback [$name]");
    }

    /**
     * Determine if a given callback exists.
     *
     * @param string $name
     *
     * @return boolean
     */
    public function hasCallback(string $name)
    {
        return array_key_exists('or' . Str::ucfirst($name), $this->proxies);
    }

    /**
     * Determine if a given proxy exists.
     *
     * @param string $proxy
     *
     * @return boolean
     */
    public function hasProxy(string $proxy)
    {
        return array_key_exists($proxy, $this->proxies);
    }

    /**
     * Load the available callbacks proxies.
     *
     * @param array $callbacks
     *
     * @return void
     */
    protected function loadProxies(array $callbacks)
    {
        $this->proxies = [];

        foreach ($callbacks as $key => $callback) {
            $this->addCallback($key, $callback);
        }
    }
}
