<?php
use PHPUnit\Framework\TestCase;

class GenerateFactoryTest extends TestCase
{
    public function testShouldHaveGetInstanceMethod()
    {
        $reflectionClass = new ReflectionClass(\Roolith\Generator\GeneratorFactory::class);

        $this->assertEquals('getInstance', $reflectionClass->getMethod('getInstance')->name);
    }
}