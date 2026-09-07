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

    public function testShouldOutputMessage()
    {
        $this->expectOutputString('hello');

        $this->console->output('hello');
    }

    public function testShouldOutputNewLine()
    {
        $this->expectOutputString(PHP_EOL);

        $this->console->outputNewLine();
    }

    public function testShouldOutputMessageWithNewLine()
    {
        $this->expectOutputString('Location: /tmp/file.php' . PHP_EOL);

        $this->console->outputLine('Location: /tmp/file.php');
    }

    public function testShouldUseConstructorArguments()
    {
        $console = new Console(['index.php', 'generate', 'controller', 'Demo']);

        $this->assertSame(['generate', 'controller', 'Demo'], $console->getArguments());
        $this->assertTrue($console->hasArgument());
    }

    public function testShouldHaveNoArgumentWhenOnlyScriptName()
    {
        $console = new Console(['index.php']);

        $this->assertSame([], $console->getArguments());
        $this->assertFalse($console->hasArgument());
    }

    public function testShouldReturnEmptyArgumentsWhenNeverSet()
    {
        $console = new Console(null);

        $this->assertSame([], $console->getArguments());
        $this->assertFalse($console->hasArgument());
    }
}