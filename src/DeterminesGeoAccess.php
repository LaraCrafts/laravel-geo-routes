<?php

namespace LaraCrafts\GeoRoutes;

use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

trait DeterminesGeoAccess
{
    /**
     * Get the countries.
     *
     * @return string[]
     */
    abstract public function getCountries();

    /**
     * Get the strategy.
     *
     * @return string
     */
    abstract public function getStrategy();

    /**
     * Determine if the request should be allowed through.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function shouldHaveAccess(Request $request)
    {
        $countries = $this->getCountries();
        $requestCountry = Location::get($request->ip())->countryCode;
        $strategy = $this->getStrategy();

        if ($strategy === 'allow') {
            return in_array($requestCountry, $countries);
        }

        return !in_array($requestCountry, $countries);
    }
}
