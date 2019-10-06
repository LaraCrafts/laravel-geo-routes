<?php

namespace LaraCrafts\GeoRoutes\Support\Facades;

use Illuminate\Support\Facades\Facade;

class CallbacksRegistrar extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'georoutes.callbacks';
    }
}
