<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use Closure;
use LaraCrafts\GeoRoutes\CallbacksRegistrar;
use Mockery;
use LaraCrafts\GeoRoutes\Tests\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;

class CallbacksRegistrarTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var \Mockery\MockInterface */
    protected $location;

    /** @var \Illuminate\Routing\Route */
    protected $route;

    /** @var \Illuminate\Routing\Router */
    protected $router;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testIfConfigCallbacksAreLoaded()
    {
        $callbacks = [
            'foo' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo',
            'bar' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::bar',
        ];

        $registrar = Mockery::mock(CallbacksRegistrar::class)->makePartial();

        $registrar->shouldReceive('addCallback')
                ->ordered()
                ->once()
                ->with('foo', '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo');

        $registrar->shouldReceive('addCallback')
                ->ordered()
                ->once()
                ->with('bar', '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::bar');

        $registrar->__construct($callbacks);
    }

    public function testIfAddCallbackAddsProxy()
    {
        $registrar = (new CallbacksRegistrar([]));
        $registrar->addCallback('foo', '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo');

        $proxies = $this->getProperty($registrar, 'proxies');

        $this->assertEquals(
            $proxies->getValue($registrar),
            ['orFoo' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo']
        );
    }

    public function testIfCallbacksFetchesTheProxiesList()
    {
        $callbacks = [
            'foo' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo',
            'bar' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::bar',
        ];

        $registrar = (new CallbacksRegistrar($callbacks));

        $proxies = $registrar->callbacks();

        foreach ($callbacks as $key => $callback) {
            $this->assertArrayHasKey('or' . ucfirst($key), $proxies);
            $this->assertEquals($callback, $proxies['or' . ucfirst($key)] ?? '');
        }
    }

    public function testIfParseCallbacksLoadsCallbacksFromClass()
    {
        $expected = [
            'orFoo' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo',
            'orBar' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::bar',
        ];

        $registrar = new CallbacksRegistrar([]);

        $registrar->parseCallbacks(\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::class);

        $proxies = $registrar->callbacks();

        foreach ($expected as $proxy => $callable) {
            $this->assertArrayHasKey($proxy, $proxies);
            $this->assertEquals(Closure::fromCallable($callable), $proxies[$proxy]);
        }
    }

    public function testIfCallbackReturnsCallable()
    {
        $registrar = new CallbacksRegistrar(['foo' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo']);

        $this->assertEquals('\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo', $registrar->callback('foo'));
        $this->assertEquals('\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo', $registrar->callback('orFoo'));
    }

    public function testIfHasCallbackReturnsTrueIfCallbackExists()
    {
        $registrar = new CallbacksRegistrar(['foo' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo']);

        $this->assertTrue($registrar->hasCallback('foo'));
    }

    public function testIfHasCallbackReturnsFalseIfCallbackDoesNotExist()
    {
        $registrar = new CallbacksRegistrar([]);

        $this->assertFalse($registrar->hasCallback('foo'));
    }

    public function testIfHasProxyReturnsTrueIfProxyExists()
    {
        $registrar = new CallbacksRegistrar(['foo' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo']);

        $this->assertTrue($registrar->hasProxy('orFoo'));
    }

    public function testIfHasProxyReturnsFalseIfProxyDoesNotExist()
    {
        $registrar = new CallbacksRegistrar([]);

        $this->assertFalse($registrar->hasProxy('orFoo'));
    }

    /**
    * Get protected or private property.
    *
    * @param mixed $mock
    * @param string $name
    *
    * @return \ReflectionProperty
    */
    protected function getProperty($class, string $name)
    {
        $reflection = new ReflectionClass($class);

        $property = $reflection->getProperty($name);

        $property->setAccessible(true);

        return $property;
    }
}
