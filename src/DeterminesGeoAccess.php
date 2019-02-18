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
     * @param boolean $strategy
     *
     * @return bool
     */
    protected function shouldHaveAccess(Request $request, array $countries, bool $allowed)
    {
        if (!$countries) {
            return !$allowed;
        }

        $countries = array_map('strtoupper', $countries);

        $requestCountry = strtoupper(Location::get($request->ip())->countryCode);

        if ($allowed) {
            return in_array($requestCountry, $countries);
        }

        return !in_array($requestCountry, $countries);
    }
}
