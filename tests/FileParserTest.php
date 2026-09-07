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

    public function testShouldReturnNullWhenTemplateDoesNotExist()
    {
        $templateDir = __DIR__. '/test-template';
        $this->instance->setDirectory($templateDir);

        $this->assertNull($this->instance->parseTemplate('xxx-missing-template', 'demo'));
    }

    public function testShouldReturnNullWhenTemplateDirectoryDoesNotExist()
    {
        $this->instance->setDirectory(sys_get_temp_dir().'/fileparser-missing-'.uniqid());

        $this->assertNull($this->instance->parseTemplate('controller', 'demo'));
    }

    public function testShouldParseEmptyTemplateWithoutError()
    {
        $templateDir = sys_get_temp_dir().'/fileparser-'.uniqid();

        $this->assertTrue(mkdir($templateDir, 0755, true));
        file_put_contents($templateDir.'/empty.txt', '');

        $this->instance->setDirectory($templateDir);

        try {
            $parsedTemplate = $this->instance->parseTemplate('empty', 'demo');

            $this->assertIsArray($parsedTemplate);
            $this->assertSame([''], $parsedTemplate['lines']);
        } finally {
            if (is_file($templateDir.'/empty.txt')) {
                unlink($templateDir.'/empty.txt');
            }

            if (is_dir($templateDir)) {
                rmdir($templateDir);
            }
        }
    }

    public function testShouldInsertPlaceholderValuesLiterally()
    {
        $templateDir = sys_get_temp_dir().'/fileparser-'.uniqid();

        $this->assertTrue(mkdir($templateDir, 0755, true));
        file_put_contents($templateDir.'/special.txt', "class {{name}} extends {name}Controller // {{name}}-{name}");

        $this->instance->setDirectory($templateDir);

        try {
            $value = 'a$1b\\c$d$0\\1';
            $parsedTemplate = $this->instance->parseTemplate('special', $value);

            $this->assertIsArray($parsedTemplate);
            $expected = 'class A$1b\\c$d$0\\1 extends a$1b\\c$d$0\\1Controller // A$1b\\c$d$0\\1-a$1b\\c$d$0\\1';
            $this->assertSame([$expected], $parsedTemplate['lines']);
        } finally {
            if (is_file($templateDir.'/special.txt')) {
                unlink($templateDir.'/special.txt');
            }

            if (is_dir($templateDir)) {
                rmdir($templateDir);
            }
        }
    }

    public function testShouldParseWithNullValue()
    {
        $this->instance->setDirectory(__DIR__.'/test-template');

        $parsedTemplate = $this->instance->parseTemplate('controller', null);

        $this->assertIsArray($parsedTemplate);
        $this->assertSame('', $parsedTemplate['instructions']['fileName']);
    }

    public function testShouldSupportCustomInstruction()
    {
        $templateDir = sys_get_temp_dir().'/fileparser-'.uniqid();
        $this->assertTrue(mkdir($templateDir, 0755, true));
        file_put_contents($templateDir.'/custom.txt', "# outputBaseDir: Controllers\n# author: Hadi\nclass {{name}} {}");

        $this->instance->setDirectory($templateDir);
        $result = $this->instance->addInstruction(['name' => 'author', 'match' => '# author:']);

        try {
            $this->assertSame($this->instance, $result);
            $parsedTemplate = $this->instance->parseTemplate('custom', 'demo');

            $this->assertSame('Controllers', $parsedTemplate['instructions']['outputBaseDir']);
            $this->assertSame('Hadi', $parsedTemplate['instructions']['author']);
            $this->assertSame(['class Demo {}'], $parsedTemplate['lines']);
        } finally {
            unlink($templateDir.'/custom.txt');
            rmdir($templateDir);
        }
    }

    public function testShouldIgnoreUnknownHashLine()
    {
        $templateDir = sys_get_temp_dir().'/fileparser-'.uniqid();
        $this->assertTrue(mkdir($templateDir, 0755, true));
        file_put_contents($templateDir.'/unknown.txt', "# outputBaseDir: Controllers\n# unknown: foo\nclass {{name}} {}");

        $this->instance->setDirectory($templateDir);

        try {
            $parsedTemplate = $this->instance->parseTemplate('unknown', 'demo');

            $this->assertArrayNotHasKey('unknown', $parsedTemplate['instructions']);
            $this->assertSame(['class Demo {}'], $parsedTemplate['lines']);
        } finally {
            unlink($templateDir.'/unknown.txt');
            rmdir($templateDir);
        }
    }

    public function testShouldTreatHashAsContentWhenPrefixIsEmpty()
    {
        $templateDir = sys_get_temp_dir().'/fileparser-'.uniqid();
        $this->assertTrue(mkdir($templateDir, 0755, true));
        file_put_contents($templateDir.'/raw.txt', "# outputBaseDir: Controllers\nclass {{name}} {}");

        $parser = new FileParser(['extension' => 'txt', 'instructionPrefix' => '']);
        $parser->setDirectory($templateDir);

        try {
            $parsedTemplate = $parser->parseTemplate('raw', 'demo');

            $this->assertArrayNotHasKey('outputBaseDir', $parsedTemplate['instructions']);
            $this->assertSame(['# outputBaseDir: Controllers', 'class Demo {}'], $parsedTemplate['lines']);
        } finally {
            unlink($templateDir.'/raw.txt');
            rmdir($templateDir);
        }
    }

    public function testShouldReturnDefaultsWhenFresh()
    {
        $parser = new FileParser();

        $this->assertNull($parser->getDirectory());
        $this->assertSame('txt', $parser->getExtension());
    }
}
