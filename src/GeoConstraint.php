<?php

namespace LaraCrafts\GeoRoutes;

class GeoConstraint
{
    /**
     * Determines if the constraint is allowing
     * requests from the specified countries
     *
     * @var boolean
     */
    protected $allowed;

    /**
     * The countries to apply the constraint on
     *
     * @var array
     */
    protected $countries;

    /**
     * The callback to execute if the country
     * is not allowed
     *
     * @var callable
     */
    protected $callback;

    /**
     * Determine if the constraint is
     * bound to a callback
     *
     * @var null|string
     */
    protected $binding = null;

    /**
     * Create a new GeoConstraint instance
     *
     * @param boolean $allowed
     * @param array $countries
     * @param callable $callback
     *
     * @return void
     */
    public function __construct(bool $allowed, array $countries, callable $callback = null)
    {
        $this->allowed = $allowed;
        $this->countries = $countries;
        $this->callback = $callback;
    }

    /**
     * Get the constraint's callback
     *
     * @return GeoCallback
     */
    public function getCallback()
    {
        if (!is_null($this->binding)) {
            return CallbacksRegistrar::resolve($this->binding);
        }

        return $this->callback;
    }

    /**
     * Set the contraint's callback
     *
     * @param GeoCallback $callback
     *
     * @return GeoCallback
     */
    public function setCallback(GeoCallback $callback)
    {
        $this->callback = $callback;

        return $this->callback;
    }

    /**
     * Bind the constraint with a callback
     *
     * @param string $callback
     * @param array|null $arguments
     *
     * @return void
     */
    public function bind(string $callback, array $arguments = null)
    {
        $this->binding = CallbacksRegistrar::bind($callback, $arguments);
    }

    /**
     * Get the countries covered by
     * the constraint
     *
     * @return array
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * Get the countries covered by
     * the constraint
     *
     * @param string ...$countries
     *
     * @return $this
     */
    public function setCountries(string ...$countries)
    {
        $this->countries = $countries;

        return $this;
    }

    /**
     * Add a country to the list of countries
     * covered by the constraint
     *
     * @param string $country
     *
     * @return $this
     */
    public function addCountry(string $country)
    {
        array_push($this->countries, $country);

        return $this;
    }

    /**
     * Set the access status
     *
     * @param boolean $allowed
     *
     * @return string
     */
    public function setAccess(bool $allowed)
    {
        $this->allowed = $allowed;

        return $this;
    }

    /**
     * Determine if the constraint allows
     * access
     *
     * @return boolean
     */
    public function isAllowed()
    {
        return $this->allowed;
    }
}
