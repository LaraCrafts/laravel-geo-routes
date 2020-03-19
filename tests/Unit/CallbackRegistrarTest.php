<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use Closure;
use InvalidArgumentException;
use LaraCrafts\GeoRoutes\CallbackRegistrar;
use LaraCrafts\GeoRoutes\Tests\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;

class CallbackRegistrarTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var \Mockery\MockInterface */
    protected $location;

    /** @var \Illuminate\Routing\Route */
    protected $route;

    /** @var \Illuminate\Routing\Router */
    protected $router;

    /** @var \LaraCrafts\GeoRoutes\CallbackRegistrar|\Mockery\MockInterface */
    protected $registrar;

    public function setUp(): void
    {
        parent::setUp();

        $this->registrar = new CallbackRegistrar;
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testIfCallbacksFetchesTheProxiesList()
    {
        $callbacks = [
            'foo' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo',
            'bar' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::bar',
        ];

        $this->registrar->loadCallbacks($callbacks);

        $proxies = $this->registrar->callbacks();

        foreach ($callbacks as $key => $callback) {
            $this->assertArrayHasKey('or' . ucfirst($key), $proxies);
            $this->assertEquals($callback, $proxies['or' . ucfirst($key)] ?? '');
        }
    }

    public function testIfCallbacksInvokesLoadCallbacksWhenArrayArgIsPresent()
    {
        $callbacks = [
            'foo' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo',
            'bar' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::bar',
        ];

        $this->registrar = Mockery::mock(CallbackRegistrar::class)->makePartial();

        $this->registrar->shouldReceive('loadCallbacks')->with($callbacks)->once();

        $this->registrar->callbacks($callbacks);
    }

    public function testIfParseCallbacksLoadsCallbacksFromClass()
    {
        $expected = [
            'orFoo' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo',
            'orBar' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::bar',
        ];

        $this->registrar->parseCallbacks(\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::class);

        $proxies = $this->registrar->callbacks();

        foreach ($expected as $proxy => $callable) {
            $this->assertArrayHasKey($proxy, $proxies);
            $this->assertEquals(Closure::fromCallable($callable), $proxies[$proxy]);
        }
    }

    public function testIfCallbackReturnsCallable()
    {
        $this->registrar->loadCallbacks(['foo' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo']);

        $this->assertEquals('\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo', $this->registrar->callback('foo'));
        $this->assertEquals('\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo', $this->registrar->callback('orFoo'));
    }

    public function testIfHasCallbackReturnsTrueIfCallbackExists()
    {
        $this->registrar->loadCallbacks(['foo' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo']);

        $this->assertTrue($this->registrar->hasCallback('foo'));
    }

    public function testIfHasCallbackReturnsFalseIfCallbackDoesNotExist()
    {
        $this->assertFalse($this->registrar->hasCallback('foo'));
    }

    public function testIfHasProxyReturnsTrueIfProxyExists()
    {
        $this->registrar->loadCallbacks(['foo' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo']);

        $this->assertTrue($this->registrar->hasProxy('orFoo'));
    }

    public function testIfHasProxyReturnsFalseIfProxyDoesNotExist()
    {
        $this->assertFalse($this->registrar->hasProxy('orFoo'));
    }

    public function testIfSetDefaultInvokesCallbackIfArgIsString()
    {
        $this->registrar = Mockery::mock(CallbackRegistrar::class)->makePartial();
        $this->registrar->loadCallbacks(['foo' => '\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo']);

        $this->registrar->shouldReceive('callback')->with('foo')->once();

        $this->registrar->setDefault('foo');
    }

    public function testIfSetDefaultSetsDefaultPropertyValueIfArgIsCallable()
    {
        $this->registrar->setDefault('\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo', 'arg1');

        $default = $this->getProperty($this->registrar, 'default')->getValue($this->registrar);

        $this->assertEquals(['\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo', ['arg1']], $default);
    }

    public function testIfSetDefaultThrowsExceptionIfArgIsInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('setDefault expects parameter 1 to be string or callable integer given');

        $this->registrar->setDefault(321314);
    }

    public function testIfGetDefaultReturnsDefaultCallbackArray()
    {
        $this->registrar->setDefault('\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo', 'arg1');
        
        $this->assertEquals(
            ['\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo', ['arg1']],
            $this->registrar->getDefault()
        );
    }

    public function testIfInvokeDefaultExecutesDefault()
    {
        $this->registrar->setDefault('\LaraCrafts\GeoRoutes\Tests\Mocks\Callbacks::foo');

        $this->assertEquals('foo', $this->registrar->invokeDefault());
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
