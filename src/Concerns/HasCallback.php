<?php

namespace LaraCrafts\GeoRoutes\Concerns;

use Illuminate\Support\Str;

trait HasCallback
{
    /**
     * The callback to execute if the visitor
     * is not allowed.
     *
     * @var array
     */
    protected $callback;

    /**
     * The callbacks' proxies.
     *
     * @var array
     */
    protected static $proxies;

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

    /**
     * Determine if a callback exists by a given name.
     *
     * @param string $name
     *
     * @return boolean
     */
    protected function callbackExists(string $name)
    {
        return array_key_exists($name, static::$proxies);
    }
}
