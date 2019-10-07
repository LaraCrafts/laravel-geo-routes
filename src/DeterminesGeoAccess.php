<?php

namespace LaraCrafts\GeoRoutes;

use Stevebauman\Location\Facades\Location;

trait DeterminesGeoAccess
{
    /**
     * Determine if the request should be allowed through.
     *
     * @param array $countries
     * @param boolean $allowed
     *
     * @return bool
     */
    protected function shouldHaveAccess(array $countries, bool $allowed)
    {
        if (!$countries) {
            return !$allowed;
        }

        $requestCountry = Location::get()->countryCode;

        if ($allowed) {
            return in_array($requestCountry, $countries);
        }

        return !in_array($requestCountry, $countries);
    }
}
