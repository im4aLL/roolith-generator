<?php
use PHPUnit\Framework\TestCase;
use Roolith\Generator\Command;
use Roolith\Generator\Commands\GenerateCommand;
use Roolith\Generator\Console;
use Roolith\Generator\FileGenerator;
use Roolith\Generator\FileParser;

class GenerateCommandTest extends TestCase
{
    private function makeCommand(string $type, string $value = 'Demo'): Command
    {
        $command = new Command();
        $command->bootstrap(['generate', $type, $value]);

        return $command;
    }

    public function testShouldOutputErrorWhenTemplateDoesNotExist()
    {
        $command = $this->makeCommand('missing-template-xxx');
        $console = new Console();
        $fileParser = new FileParser();
        $fileParser->setDirectory(__DIR__.'/test-template');
        $fileGenerator = new FileGenerator();
        $fileGenerator->setProjectBaseDir(sys_get_temp_dir().'/gen-cmd-'.uniqid());

        $this->expectOutputString("Template 'missing-template-xxx' doesn't exist!".PHP_EOL);

        $handler = new GenerateCommand();
        $result = $handler->handle($command, $console, $fileParser, $fileGenerator);

        $this->assertSame($handler, $result);
    }

    public function testShouldNotCreateFileWhenTemplateIsMissing()
    {
        $baseDir = sys_get_temp_dir().'/gen-cmd-'.uniqid();
        mkdir($baseDir, 0755, true);

        $command = $this->makeCommand('missing-template-xxx');
        $console = new Console();
        $fileParser = new FileParser();
        $fileParser->setDirectory(__DIR__.'/test-template');
        $fileGenerator = new FileGenerator();
        $fileGenerator->setProjectBaseDir($baseDir);

        $this->expectOutputString("Template 'missing-template-xxx' doesn't exist!".PHP_EOL);

        try {
            (new GenerateCommand())->handle($command, $console, $fileParser, $fileGenerator);

            $this->assertEquals(['.', '..'], scandir($baseDir));
        } finally {
            $fileGenerator->deleteDir($baseDir);
        }
    }

    public function testShouldCreateFileWhenTemplateExists()
    {
        $baseDir = sys_get_temp_dir().'/gen-cmd-'.uniqid();
        mkdir($baseDir, 0755, true);

        $command = $this->makeCommand('controller', 'DemoForGenCmd');
        $console = new Console();
        $fileParser = new FileParser();
        $fileParser->setDirectory(__DIR__.'/test-template');
        $fileGenerator = new FileGenerator();
        $fileGenerator->setProjectBaseDir($baseDir);

        $this->expectOutputRegex('/DemoForGenCmd\.php has been created!/');

        try {
            $result = (new GenerateCommand())->handle($command, $console, $fileParser, $fileGenerator);

            $this->assertInstanceOf(GenerateCommand::class, $result);
            $createdFile = $baseDir.'/Controllers/DemoForGenCmd.php';
            $this->assertTrue(is_file($createdFile));
            $this->assertStringContainsString('class DemoForGenCmd', (string) file_get_contents($createdFile));
        } finally {
            $fileGenerator->deleteDir($baseDir);
        }
    }

    public function testShouldOutputErrorWhenParseFails()
    {
        $command = $this->makeCommand('controller', 'Demo');

        $fileParser = $this->createMock(FileParser::class);
        $fileParser->method('templateExists')->willReturn(true);
        $fileParser->method('parseTemplate')->willReturn(null);

        $console = new Console();
        $fileGenerator = new FileGenerator();

        $this->expectOutputString('Unable to parse template!'.PHP_EOL);

        (new GenerateCommand())->handle($command, $console, $fileParser, $fileGenerator);
    }

    public function testShouldOutputErrorWhenParseResultIsMalformed()
    {
        $command = $this->makeCommand('controller', 'Demo');

        $fileParser = $this->createMock(FileParser::class);
        $fileParser->method('templateExists')->willReturn(true);
        $fileParser->method('parseTemplate')->willReturn(['lines' => ['hello']]);

        $console = new Console();
        $fileGenerator = new FileGenerator();
        $fileGenerator->setProjectBaseDir(sys_get_temp_dir().'/gen-cmd-'.uniqid());

        $this->expectOutputString('Unable to parse template!'.PHP_EOL);

        (new GenerateCommand())->handle($command, $console, $fileParser, $fileGenerator);
    }

    public function testShouldOutputUsageWhenTemplateTypeIsMissing()
    {
        $command = new Command();
        $command->bootstrap(['generate']);
        $console = new Console();
        $fileParser = new FileParser();
        $fileParser->setDirectory(__DIR__.'/test-template');
        $fileGenerator = new FileGenerator();
        $baseDir = sys_get_temp_dir().'/gen-cmd-'.uniqid();
        mkdir($baseDir, 0755, true);
        $fileGenerator->setProjectBaseDir($baseDir);

        $this->expectOutputString('Missing template type. Usage: generate <type> <Name>'.PHP_EOL);

        try {
            $result = (new GenerateCommand())->handle($command, $console, $fileParser, $fileGenerator);

            $this->assertInstanceOf(GenerateCommand::class, $result);
            $this->assertEquals(['.', '..'], scandir($baseDir));
        } finally {
            $fileGenerator->deleteDir($baseDir);
        }
    }
}
