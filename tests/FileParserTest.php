<?php
use PHPUnit\Framework\TestCase;
use Roolith\Generator\FileParser;

class FileParserTest extends TestCase
{
    public $instance;

    public function setUp(): void
    {
        $this->instance = new FileParser();
    }

    public function testShouldAddDefaultInstructions()
    {
        $instructions = $this->instance->getInstructions();

        $this->assertCount(1, $instructions);
    }

    public function testShouldSetFileExtension()
    {
        $this->instance->setFileExtension('txt');

        $this->assertEquals('txt', $this->instance->getExtension());
    }

    public function testShouldSetDirectory()
    {
        $this->instance->setDirectory(__DIR__);

        $this->assertEquals(__DIR__, $this->instance->getDirectory());
    }

    public function testShouldCheckWhetherTemplateExistsOrNot()
    {
        $templateDir = __DIR__. '/test-template';
        $this->instance->setDirectory($templateDir);

        $this->assertTrue($this->instance->templateExists('controller'));
        $this->assertFalse($this->instance->templateExists('xxx'));

        $this->instance->setFileExtension('xxx');
        $this->assertTrue($this->instance->templateExists('command'));
    }

    public function testShouldParseTemplate()
    {
        $templateDir = __DIR__. '/test-template';
        $this->instance->setDirectory($templateDir);

        $parsedTemplate = $this->instance->parseTemplate('controller', 'demo');
        $this->assertIsArray($parsedTemplate);
        $this->assertIsArray($parsedTemplate['instructions']);
        $this->assertIsArray($parsedTemplate['lines']);
    }
}