<?php
use PHPUnit\Framework\TestCase;

class GenerateFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        \Roolith\Generator\GeneratorFactory::reset();

        parent::tearDown();
    }

    public function testShouldHaveGetInstanceMethod()
    {
        $reflectionClass = new ReflectionClass(\Roolith\Generator\GeneratorFactory::class);

        $this->assertEquals('getInstance', $reflectionClass->getMethod('getInstance')->name);
    }

    public function testShouldReturnSameInstanceWithoutReset()
    {
        $first = \Roolith\Generator\GeneratorFactory::getInstance();
        $second = \Roolith\Generator\GeneratorFactory::getInstance();

        $this->assertSame($first, $second);
    }

    public function testShouldReturnNewInstanceAfterReset()
    {
        $first = \Roolith\Generator\GeneratorFactory::getInstance();

        \Roolith\Generator\GeneratorFactory::reset();

        $second = \Roolith\Generator\GeneratorFactory::getInstance();

        $this->assertNotSame($first, $second);
    }

    public function testShouldPreventCloning()
    {
        $reflectionClass = new ReflectionClass(\Roolith\Generator\GeneratorFactory::class);
        $instance = $reflectionClass->newInstanceWithoutConstructor();

        $this->expectException(Error::class);

        clone $instance;
    }

    public function testShouldPreventUnserializing()
    {
        $reflectionClass = new ReflectionClass(\Roolith\Generator\GeneratorFactory::class);
        $instance = $reflectionClass->newInstanceWithoutConstructor();
        $serialized = serialize($instance);

        $this->expectException(LogicException::class);

        unserialize($serialized);
    }

    public function testShouldReturnGeneratorInstance()
    {
        $instance = \Roolith\Generator\GeneratorFactory::getInstance();

        $this->assertInstanceOf(\Roolith\Generator\Generator::class, $instance);
    }

    public function testShouldPreventUnserializeCallbackDirectly()
    {
        $reflectionClass = new ReflectionClass(\Roolith\Generator\GeneratorFactory::class);
        $instance = $reflectionClass->newInstanceWithoutConstructor();

        $this->expectException(LogicException::class);

        $instance->__unserialize([]);
    }
}