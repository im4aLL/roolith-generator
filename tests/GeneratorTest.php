<?php
require_once __DIR__. '/TestMockCommandClass.php';

use PHPUnit\Framework\TestCase;
use Roolith\Generator\Command;
use Roolith\Generator\Console;
use Roolith\Generator\FileGenerator;
use Roolith\Generator\FileParser;
use Roolith\Generator\Generator;

class GeneratorTest extends TestCase
{
    public $instance;

    public function setUp(): void
    {
        $console = new Console();
        $fileParser = new FileParser();
        $command = new Command();
        $fileGenerator = new FileGenerator();

        $this->instance = new Generator($console, $fileParser, $command, $fileGenerator);
    }

    public function testShouldCreateInstance()
    {
        $this->assertNotEmpty($this->instance);
    }

    public function testShouldSetTemplateDirectory()
    {
        $templateDirectory = 'test_directory';
        $result = $this->instance->setTemplateDirectory($templateDirectory);

        $this->assertEquals($result, $this->instance);
    }

    public function testShouldSetProjectBaseDirectory()
    {
        $templateDirectory = 'test_directory';
        $result = $this->instance->setProjectBaseDirectory($templateDirectory);

        $this->assertEquals($result, $this->instance);
    }

    public function testShouldHaveWatchMethod()
    {
        $this->assertTrue(method_exists($this->instance, 'watch'));
    }

    public function testShouldRegisterCustomCommand()
    {
        $result = $this->instance->registerCommandClass([
            TestMockCommandClass::class,
        ]);

        $this->assertEquals($result, $this->instance);
    }

    public function testShouldThrowWhenCommandClassDoesNotExist()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/does not exist/");

        $this->instance->registerCommandClass([
            'NonExistent\\CommandClass',
        ]);
    }

    public function testShouldThrowWhenCommandClassDoesNotImplementInterface()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/must implement CommandInterface/");

        $this->instance->registerCommandClass([
            stdClass::class,
        ]);
    }

    public function testShouldThrowWhenCommandClassIsNotAString()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->instance->registerCommandClass([
            123,
        ]);
    }

    public function testShouldThrowWhenCommandClassListIsNotAnArray()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/must be an array/");

        $this->instance->registerCommandClass('TestMockCommandClass');
    }

    public function testShouldThrowWhenCommandClassIsInterface()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/must implement CommandInterface/");

        $this->instance->registerCommandClass([
            \Roolith\Generator\Interfaces\CommandInterface::class,
        ]);
    }

    public function testShouldThrowWhenCommandClassIsAbstract()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/must be instantiable/");

        $this->instance->registerCommandClass([
            AbstractTestMockCommand::class,
        ]);
    }

    public function testShouldThrowWhenCommandClassRequiresConstructorArguments()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/no required constructor parameters/");

        $this->instance->registerCommandClass([
            RequiredArgsTestMockCommand::class,
        ]);
    }
}

abstract class AbstractTestMockCommand implements \Roolith\Generator\Interfaces\CommandInterface
{
    abstract public function register(): array;

    abstract public function handle(
        \Roolith\Generator\Command $command,
        \Roolith\Generator\Console $console,
        \Roolith\Generator\FileParser $fileParser,
        \Roolith\Generator\FileGenerator $fileGenerator
    ): mixed;
}

class RequiredArgsTestMockCommand implements \Roolith\Generator\Interfaces\CommandInterface
{
    public function __construct($required)
    {
    }

    public function register(): array
    {
        return ['name' => 'required-args'];
    }

    public function handle(
        \Roolith\Generator\Command $command,
        \Roolith\Generator\Console $console,
        \Roolith\Generator\FileParser $fileParser,
        \Roolith\Generator\FileGenerator $fileGenerator
    ): mixed {
    }
}