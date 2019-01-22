<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GeoRoutes Configuration
    |--------------------------------------------------------------------------
    | [+] callbacks:
    | In the `callback` key, you can add any callbacks that you might want to call
    | if a user is not allowed to access a route.
    |
    | If you, for example, add a callback under the key 'myCallback' you will
    | be able to chain it like so:
    |
    | Route::get(...)->allowFrom(...)->orMyCallback(...);
    |
    | [+] defaults:
    | This the default configuration that will be used if you only specify the countries
    |
    | + The `CALLBACK` key is the default callback to be executed be sure to convert your
    | callback to studly-case (e.g. MyCallback instead of myCallback) and prefix it with
    | `or` (e.g. orMyCallback instead of MyCallback)
    | The callback `args` can be `null` or an empty array if your callback doesn't accept
    | any parameters.
    |
    | + The `RULE` key is the default rule to be used while using the `from()` method
    | e.g. Route::get(...)->from(...); If you don't override the default rule by using
    | the `allow()` or `deny()` methods the default rule will be applied.
    |
    |
    */
    'callbacks' => [
        // 'myCallback' => 'MyClass::myFunction',
    ],
    'defaults' => [

        'RULE' => 'allow', //Can be either `allow` or `deny`, anything else will set this to `deny` automatically.

        'CALLBACK' => [
            'name' => 'orUnauthorized',
            'args' => null,
        ],

    ],
];
