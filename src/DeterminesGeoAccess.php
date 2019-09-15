<?php

namespace LaraCrafts\GeoRoutes;

use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

trait DeterminesGeoAccess
{
    /**
     * Determine if the request should be allowed through.
     *
     * @param array $countries
     * @param string $strategy
     * @return bool
     */
    protected function shouldHaveAccess(array $countries, string $strategy)
    {
        if (!$countries) {
            return $strategy !== 'allow';
        }

        $requestCountry = Location::get()->countryCode;

        if ($strategy === 'allow') {
            return in_array($requestCountry, $countries);
        }

        return !in_array($requestCountry, $countries);
    }
}
