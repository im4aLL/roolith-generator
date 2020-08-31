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
}