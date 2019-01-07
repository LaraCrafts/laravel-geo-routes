<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use LaraCrafts\GeoRoutes\GeoCountriesTrait;
use LaraCrafts\GeoRoutes\Tests\TestCase;

class GeoCountriesTest extends TestCase
{
    use GeoCountriesTrait;

    /**
     * @test
     * @medium
     */
    public function getCountryNameReturnsCountryNameForValidCountryCode()
    {
        $countryName = $this->getCountryName('tn');
        $this->assertEquals('Tunisia', $countryName);
    }
    
    /**
     * @test
     * @medium
     * @expectedException \InvalidArgumentException
     */
    public function getCountryNameThrowsExceptionForInvalidCountryCode()
    {
        $this->getCountryName('INVALID');
    }

    /**
     * @test
     * @medium
     */
    public function getCountryCodeReturnsCodeForValidCountryName()
    {
        $countryCode = $this->getCountryCode('Canada');
        $this->assertEquals('CA', $countryCode);
    }

    /**
     * @test
     * @medium
     * @expectedException \InvalidArgumentException
     */
    public function getCountryCodeThrowsExceptionForInvalidCountryName()
    {
        $this->getCountryCode('INVALID');
    }

    /**
     * @test
     * @small
     */
    public function isValidCountryCodeReturnsTrueForValidCountryCode()
    {
        $this->assertTrue($this->isValidCountryCode('us'));
    }

    /**
     * @test
     * @small
     */
    public function isValidCountryCodeReturnsFalseForInvalidCountryCode()
    {
        $this->assertFalse($this->isValidCountryCode('INVALID'));
    }

    /**
     * @test
     * @small
     */
    public function isValidCountryNameReturnsTrueForInvalidCountryName()
    {
        $this->assertTrue($this->isValidCountryName('United States'));
    }

    /**
     * @test
     * @small
     */
    public function isValidCountryNameReturnsFalseForInvalidCountryName()
    {
        $this->assertFalse($this->isValidCountryName('INVALID'));
    }
}
