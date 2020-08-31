<?php
use PHPUnit\Framework\TestCase;
use Roolith\Generator\Console;

class ConsoleTest extends TestCase
{
    public $console;

    public function setUp(): void
    {
        $this->console = new Console();
    }

    public function testShouldSetArguments()
    {
        $this->assertIsArray($this->console->getArguments());
    }

    public function testShouldRemoveFirstItemFromArgument()
    {
        $array = [1, 2, 3];
        $this->console->setArguments($array);

        $this->assertCount(2, $this->console->getArguments());
    }

    public function testShouldReturnBoolForHasArguments()
    {
        $this->assertFalse($this->console->hasArgument());

        $array = [1, 2, 3];
        $this->console->setArguments($array);

        $this->assertTrue($this->console->hasArgument());
    }
}