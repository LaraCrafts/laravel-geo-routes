<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Global switch
    |--------------------------------------------------------------------------
    |
    | Enable or disable the global (application-wide) middleware using the
    | switch below.
    |
    */
    'enabled' => env('GEO_ACCESS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Allowed
    |--------------------------------------------------------------------------
    |
    | The allowed key has a value of type boolean which determines the current
    | access constraint.
    |
    | If set to `true`, all requests from the `countries` entered below will be
    | allowed
    | Otherwise, if set to `false`, access from the listed countries will be denied.
    |
    */
    'allowed' => false,

    /*
    |--------------------------------------------------------------------------
    | Countries
    |--------------------------------------------------------------------------
    |
    | Below you can add one or multiple countries that should either be
    | allowed or denied. Every value entered here must be a valid ISO 3166-1
    | alpha-2 country code, for more information see:
    |
    | https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
    |
    */
    'countries' => [
        //
    ],

];
