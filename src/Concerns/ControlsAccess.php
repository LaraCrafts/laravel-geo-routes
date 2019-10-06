<?php

namespace LaraCrafts\GeoRoutes\Concerns;

trait ControlsAccess
{
    /**
     * Determines whether to allow or deny access.
     *
     * @var string
     */
    protected $strategy;

    /**
     * The countries to apply the rule for.
     *
     * @var array
     */
    protected $countries;

    /**
     * Allow access from the given countries.
     *
     * @param string ...$countries
     *
     * @return $this
     */
    public function allowFrom(string ...$countries)
    {
        $this->strategy = 'allow';
        $this->countries = $countries;

        return $this;
    }

    /**
     * Deny access from the given countries.
     *
     * @param string ...$countries
     *
     * @return $this
     */
    public function denyFrom(string ...$countries)
    {
        $this->strategy = 'deny';
        $this->countries = $countries;
    }

    /**
     * Allow given countries.
     *
     * @return $this
     */
    public function allow()
    {
        $this->strategy = 'allow';

        return $this;
    }

    /**
     * Deny given countries.
     *
     * @return $this
     */
    public function deny()
    {
        $this->strategy = 'deny';

        return $this;
    }
}
