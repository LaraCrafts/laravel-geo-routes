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
    | Strategy
    |--------------------------------------------------------------------------
    |
    | The strategy determines what should be done with the country codes
    | entered under 'countries'. The strategy will default to 'deny' if no
    | valid value is set.
    |
    | Allowed values: allow, deny
    |
    */
    'strategy' => 'deny',

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
