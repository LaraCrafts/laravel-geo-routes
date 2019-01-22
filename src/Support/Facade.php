<?php

namespace LaraCrafts\GeoRoutes\Support;

use Illuminate\Support\Facades\Route;
use LaraCrafts\GeoRoutes\GeoRoute;

class Facade extends Route
{
    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        $resolved = parent::__callStatic($method, $args);

        if (!$resolved instanceof \Illuminate\Routing\Route) {
            return $resolved;
        }

        return new GeoRoute($resolved, []);
    }
}
