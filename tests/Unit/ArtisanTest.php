<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use LaraCrafts\GeoRoutes\Tests\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use LaraCrafts\GeoRoutes\Console\Commands\RouteListCommand;

class ArtisanTest extends TestCase
{
    /** @var \Illuminate\Routing\Router */
    protected $router;

    /** @var \Symfony\Component\Console\Tester\CommandTester */
    protected $tester;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->router = $this->app->make('router');
        $this->tester = $this->getTester();
        $this->setUpRoutes();
    }

    /** @test */
    public function geoAddsConstraintColumns()
    {
        $this->tester->execute(['--geo' => true]);
        $output = $this->tester->getDisplay();

        $this->assertStringContainsString('Countries', $output);
        $this->assertStringContainsString('Strategy', $output);
        $this->assertStringContainsString('Callback', $output);
    }

    public function testIfGeoOnlyDisplaysOnlyGeoRoutes()
    {
        $this->tester->execute(['--geo-only' => true]);
        $output = $this->tester->getDisplay();

        $this->assertStringContainsString('home', $output);
        $this->assertStringContainsString('posts', $output);
        $this->assertStringNotContainsString('timeline', $output);
    }

    /** @test */
    public function countryDisplaysOnlyRoutesWithGivenCountry()
    {
        $this->tester->execute(['--country' => 'NL']);
        $output = $this->tester->getDisplay();

        $this->assertStringContainsString('home', $output);
        $this->assertStringNotContainsString('posts', $output);
        $this->assertStringNotContainsString('timeline', $output);
    }

    /** @test */
    public function strategyDisplaysOnlyRoutesWithGivenStrategy()
    {
        $this->tester->execute(['--strategy' => 'allow']);
        $output = $this->tester->getDisplay();

        $this->assertStringContainsString('posts', $output);
        $this->assertStringNotContainsString('home', $output);
        $this->assertStringNotContainsString('timeline', $output);

        $this->tester->execute(['--strategy' => 'deny']);
        $output = $this->tester->getDisplay();

        $this->assertStringContainsString('home', $output);
        $this->assertStringNotContainsString('posts', $output);
        $this->assertStringNotContainsString('timeline', $output);
    }

    protected function setUpRoutes()
    {
        $this->router->get(
            '/home',
            [
                'uses' => '\LaraCrafts\GeoRoutes\Tests\Mocks\MockController@index',
                'geo' => [
                    'strategy' => 'deny',
                    'countries' => ['NL', 'GB'],
                    'callback' => ['\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo', []]
                ],
            ]
        );

        $this->router->get(
            '/posts',
            [
                'uses' => '\LaraCrafts\GeoRoutes\Tests\Mocks\MockController@index',
                'geo' => [
                    'strategy' => 'allow',
                    'countries' => ['CA', 'DZ', 'ES'],
                    'callback' => ['\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo', []],
                ]
            ]
        );

        $this->router->get('/timeline', ['uses' => '\LaraCrafts\GeoRoutes\Tests\Mocks\MockController@index']);
    }

    protected function getTester()
    {   
        $this->command = new RouteListCommand($this->router);
        $this->command->setLaravel($this->app);
        
        return new CommandTester($this->command);
    }
}