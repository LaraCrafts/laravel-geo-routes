<?php

namespace LaraCrafts\GeoRoutes;

use BadMethodCallException;
use InvalidArgumentException;

trait CallbacksHandler
{
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
        $this->handleCallbacks($method, $arguments);
    }

    /**
     * Dynamically handle callbacks
     *
     * @param string $method
     * @param array $arguments
     *
     * @return void
     */
    protected function handleCallbacks(string $method, array $arguments)
    {
        if (isset($this->route) && method_exists($this->route, $method)) {
            return $this->route->$method(...$arguments);
        }

        if (CallbacksRegistrar::has($method)) {
            return $this->constraint
                        ->bind($method, $arguments);
        }

        if ($method == 'or') {
            if (count($arguments) == 0 || !is_callable($arguments[0])) {
                throw new InvalidArgumentException("The 'or' callback expects one parameter of type callable.");
            }

            $callback = new GeoCallback('or', $arguments[0]);

            return $this->constraint->setCallback($callback);
        }

        throw new BadMethodCallException("Undefined method '$method'");
    }
}
