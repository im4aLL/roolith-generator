<?php

use PHPUnit\Framework\TestCase;
use Roolith\Generator\Console;
use Roolith\Generator\FileGenerator;
use Roolith\Generator\FileParser;

class FileGeneratorTest extends TestCase
{
    public $instance;
    public $console;
    public $fileParser;

    public function setUp(): void
    {
        $this->instance = new FileGenerator();
        $this->console = new Console();
        $this->fileParser = new FileParser();
    }

    public function testShouldSetFileExtension()
    {
        $this->instance->setFileExtension('xxx');

        $this->assertEquals('xxx', $this->instance->getConfig()['extension']);
    }

    public function testShouldSetProjectDirectory()
    {
        $result = $this->instance->setProjectBaseDir(__DIR__);

        $this->assertSame($this->instance, $result);
    }

    public function testShouldSaveFile()
    {
        $this->instance->setProjectBaseDir(__DIR__);
        $parsedTemplateData = $this->fileParser->setDirectory(__DIR__.'/test-template')->parseTemplate('controller', 'demo');
        $saved = $this->instance->save($parsedTemplateData['lines'], $parsedTemplateData['instructions'], $this->console);
        $fileExists = file_exists(__DIR__.'/'.$parsedTemplateData['instructions']['outputBaseDir'].'/DemoController.'.$this->instance->getConfig()['extension']);
        $this->instance->deleteDir(__DIR__.'/'.$parsedTemplateData['instructions']['outputBaseDir']);

        $this->assertTrue($saved['created']);
        $this->assertTrue($fileExists);
    }
}