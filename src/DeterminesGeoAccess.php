<?php

namespace LaraCrafts\GeoRoutes;

use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

trait DeterminesGeoAccess
{
    /**
     * Determine if the request should be allowed through.
     *
     * @param \Illuminate\Http\Request $request
     * @param array $countries
     * @param string $strategy
     * @return bool
     */
    protected function shouldHaveAccess(Request $request, array $countries, string $strategy)
    {
        if (!$countries) {
            return $strategy !== 'allow';
        }

        $requestCountry = Location::get($request->ip())->countryCode;

        if ($strategy === 'allow') {
            return in_array($requestCountry, $countries);
        }

        return !in_array($requestCountry, $countries);
    }
}
