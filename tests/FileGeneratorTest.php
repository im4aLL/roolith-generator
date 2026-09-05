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
        $parsedTemplateData = $this->fileParser->setDirectory(__DIR__.'/test-template')->parseTemplate('controller', 'demoController');
        $saved = $this->instance->save($parsedTemplateData['lines'], $parsedTemplateData['instructions'], $this->console);
        $fileExists = file_exists(__DIR__.'/'.$parsedTemplateData['instructions']['outputBaseDir'].'/DemoController.'.$this->instance->getConfig()['extension']);
        $this->instance->deleteDir(__DIR__.'/'.$parsedTemplateData['instructions']['outputBaseDir']);

        $this->assertTrue($saved['created']);
        $this->assertTrue($fileExists);
    }

    public function testShouldSaveFileWithoutOutputBaseDir()
    {
        $baseDir = sys_get_temp_dir().'/filegen-'.uniqid();
        mkdir($baseDir, 0755, true);
        $this->instance->setProjectBaseDir($baseDir);

        $saved = $this->instance->save(['hello'], ['fileName' => 'NoDirFile'], $this->console);

        $this->assertTrue($saved['created']);
        $this->assertEquals($baseDir.'/NoDirFile.php', $saved['completeFilePath']);
        $this->assertTrue(file_exists($saved['completeFilePath']));

        unlink($saved['completeFilePath']);
        rmdir($baseDir);
    }

    public function testShouldSaveFileWithEmptyInstructions()
    {
        $baseDir = sys_get_temp_dir().'/filegen-'.uniqid();
        mkdir($baseDir, 0755, true);
        $this->instance->setProjectBaseDir($baseDir);

        $saved = $this->instance->save(['hello'], [], $this->console);

        $this->assertTrue($saved['created']);
        $this->assertEquals($baseDir.'/.php', $saved['completeFilePath']);
        $this->assertTrue(file_exists($saved['completeFilePath']));

        unlink($saved['completeFilePath']);
        rmdir($baseDir);
    }

    public function testShouldConfirmOverwriteOnYesVariants()
    {
        foreach (['yes', 'YES', 'Yes', 'y', 'Y', "  yes  \n", "  Y  \n"] as $input) {
            $handle = $this->givenInputStream($input);
            $this->assertTrue($this->invokeGetOverwriteConfirmation($handle), 'Failed for input: '.var_export($input, true));
            $this->assertFalse(is_resource($handle), 'Handle leaked for input: '.var_export($input, true));
        }
    }

    public function testShouldDeclineOverwriteOnNoVariantsAndEmpty()
    {
        foreach (['no', 'NO', 'n', 'N', '', "\n", 'ye', 'yess', 'nope'] as $input) {
            $handle = $this->givenInputStream($input);
            $this->assertFalse($this->invokeGetOverwriteConfirmation($handle), 'Failed for input: '.var_export($input, true));
            $this->assertFalse(is_resource($handle), 'Handle leaked for input: '.var_export($input, true));
        }
    }

    public function testShouldDeclineOverwriteOnEof()
    {
        $handle = fopen('php://memory', 'r');
        $this->assertFalse($this->invokeGetOverwriteConfirmation($handle));
        $this->assertFalse(is_resource($handle));
    }

    private function givenInputStream($content)
    {
        $handle = fopen('php://memory', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        return $handle;
    }

    private function invokeGetOverwriteConfirmation($handle)
    {
        $method = new \ReflectionMethod(FileGenerator::class, 'getOverwriteConfirmation');
        $method->setAccessible(true);

        return $method->invoke($this->instance, $handle);
    }
}
