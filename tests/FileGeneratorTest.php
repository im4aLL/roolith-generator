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

    public function testDefaultProjectBaseDirIsNotRoot()
    {
        $fresh = new FileGenerator();
        $property = new \ReflectionProperty(FileGenerator::class, 'projectBaseDir');
        $property->setAccessible(true);

        $this->assertNotEquals('/', $property->getValue($fresh));
    }

    public function testShouldCreateNestedOutputDirsOnSave()
    {
        $baseDir = sys_get_temp_dir().'/filegen-'.uniqid();
        mkdir($baseDir, 0755, true);
        $this->instance->setProjectBaseDir($baseDir);

        $saved = $this->instance->save(['hello'], ['outputBaseDir' => 'nested/deep', 'fileName' => 'NestedFile'], $this->console);

        $this->assertTrue($saved['created']);
        $this->assertStringStartsWith($baseDir, $saved['completeFilePath']);
        $this->assertTrue(is_file($saved['completeFilePath']));

        $this->assertTrue($this->instance->deleteDir($baseDir));
        $this->assertFalse(is_dir($baseDir));
    }

    public function testWriteFileReturnsFalseWhenDirectoryIsMissing()
    {
        $missingPath = sys_get_temp_dir().'/filegen-missing-'.uniqid().'/file.php';

        $this->assertFalse($this->invokeWriteFile($missingPath, 'content'));
    }

    public function testWriteFileReturnsFalseWhenPathIsDirectory()
    {
        $baseDir = sys_get_temp_dir().'/filegen-'.uniqid();
        mkdir($baseDir, 0755, true);

        $this->assertFalse($this->invokeWriteFile($baseDir, 'content'));

        rmdir($baseDir);
    }

    public function testSaveReturnsFalseWhenOutputDirIsAFile()
    {
        $baseDir = sys_get_temp_dir().'/filegen-'.uniqid();
        mkdir($baseDir, 0755, true);
        $blockingFile = $baseDir.'/blocker';
        file_put_contents($blockingFile, 'blocker');

        $this->instance->setProjectBaseDir($baseDir);

        $saved = $this->instance->save(['hello'], ['outputBaseDir' => 'blocker', 'fileName' => 'Nope'], $this->console);

        $this->assertFalse($saved['created']);

        unlink($blockingFile);
        rmdir($baseDir);
    }

    public function testDeleteDirReturnsFalseOnMissingDir()
    {
        $missing = sys_get_temp_dir().'/filegen-missing-'.uniqid();

        $this->assertFalse($this->instance->deleteDir($missing));
    }

    public function testDeleteDirRefusesEmptyAndRoot()
    {
        $this->assertFalse($this->instance->deleteDir(''));
        $this->assertFalse($this->instance->deleteDir('/'));
    }

    public function testDeleteDirDeletesNestedStructure()
    {
        $baseDir = sys_get_temp_dir().'/filegen-'.uniqid();
        mkdir($baseDir.'/sub/nested', 0755, true);
        file_put_contents($baseDir.'/a.txt', 'a');
        file_put_contents($baseDir.'/sub/b.txt', 'b');

        $this->assertTrue($this->instance->deleteDir($baseDir));
        $this->assertFalse(is_dir($baseDir));
    }

    public function testDeleteDirHandlesTrailingSlash()
    {
        $baseDir = sys_get_temp_dir().'/filegen-'.uniqid();
        mkdir($baseDir, 0755, true);
        file_put_contents($baseDir.'/a.txt', 'a');

        $this->assertTrue($this->instance->deleteDir($baseDir.'/'));
        $this->assertFalse(is_dir($baseDir));
    }

    public function testDeleteDirDeletesDotfiles()
    {
        $baseDir = sys_get_temp_dir().'/filegen-'.uniqid();
        mkdir($baseDir, 0755, true);
        file_put_contents($baseDir.'/.hidden', 'hidden');
        file_put_contents($baseDir.'/.gitkeep', '');
        file_put_contents($baseDir.'/visible.txt', 'visible');

        $this->assertTrue($this->instance->deleteDir($baseDir));
        $this->assertFalse(is_dir($baseDir));
    }

    public function testDeleteDirUnlinksSymlinkWithoutRecursing()
    {
        $baseDir = sys_get_temp_dir().'/filegen-'.uniqid();
        $targetDir = sys_get_temp_dir().'/filegen-target-'.uniqid();
        mkdir($baseDir, 0755, true);
        mkdir($targetDir, 0755, true);
        file_put_contents($targetDir.'/keep.txt', 'keep');
        symlink($targetDir, $baseDir.'/link-to-target');
        file_put_contents($baseDir.'/local.txt', 'local');

        $this->assertTrue($this->instance->deleteDir($baseDir));
        $this->assertFalse(is_dir($baseDir));
        $this->assertTrue(is_file($targetDir.'/keep.txt'));

        unlink($targetDir.'/keep.txt');
        rmdir($targetDir);
    }

    public function testDeleteDirUnlinksFileSymlink()
    {
        $baseDir = sys_get_temp_dir().'/filegen-'.uniqid();
        mkdir($baseDir, 0755, true);
        $outside = sys_get_temp_dir().'/filegen-outside-'.uniqid().'.txt';
        file_put_contents($outside, 'outside');
        symlink($outside, $baseDir.'/link.txt');

        $this->assertTrue($this->instance->deleteDir($baseDir));
        $this->assertFalse(is_dir($baseDir));
        $this->assertTrue(is_file($outside));

        unlink($outside);
    }

    public function testSetProjectBaseDirRejectsEmptySlashAndNonString()
    {
        $baseDir = sys_get_temp_dir().'/filegen-'.uniqid();
        mkdir($baseDir, 0755, true);
        $this->instance->setProjectBaseDir($baseDir);

        $property = new \ReflectionProperty(FileGenerator::class, 'projectBaseDir');
        $property->setAccessible(true);

        $this->instance->setProjectBaseDir('');
        $this->assertEquals($baseDir, $property->getValue($this->instance));

        $this->instance->setProjectBaseDir('/');
        $this->assertEquals($baseDir, $property->getValue($this->instance));

        $this->instance->setProjectBaseDir(null);
        $this->assertEquals($baseDir, $property->getValue($this->instance));

        $this->assertSame($this->instance, $this->instance->setProjectBaseDir(''));

        rmdir($baseDir);
    }

    public function testSaveRefusesTraversalAndAbsolutePaths()
    {
        $baseDir = sys_get_temp_dir().'/filegen-'.uniqid();
        mkdir($baseDir, 0755, true);
        $this->instance->setProjectBaseDir($baseDir);

        $traversal = $this->instance->save(['hello'], ['outputBaseDir' => '../escape', 'fileName' => 'Nope'], $this->console);
        $this->assertFalse($traversal['created']);
        $this->assertFalse(is_file($baseDir.'/../escape/Nope.php'));

        $absolute = $this->instance->save(['hello'], ['outputBaseDir' => '/tmp/absolute-escape', 'fileName' => 'Nope'], $this->console);
        $this->assertFalse($absolute['created']);
        $this->assertFalse(is_file('/tmp/absolute-escape/Nope.php'));

        $slashName = $this->instance->save(['hello'], ['fileName' => 'sub/evil'], $this->console);
        $this->assertFalse($slashName['created']);
        $this->assertFalse(is_file($baseDir.'/sub/evil.php'));

        $dotdotName = $this->instance->save(['hello'], ['fileName' => '..'], $this->console);
        $this->assertFalse($dotdotName['created']);

        $this->assertTrue(is_dir($baseDir));
        rmdir($baseDir);
    }

    public function testDeleteDirRefusesDotAndDotDot()
    {
        $cwd = getcwd();
        $marker = $cwd.'/filegen-dot-marker-'.uniqid().'.txt';
        file_put_contents($marker, 'marker');

        $this->assertFalse($this->instance->deleteDir('.'));
        $this->assertFalse($this->instance->deleteDir('..'));
        $this->assertFalse($this->instance->deleteDir('./'));
        $this->assertFalse($this->instance->deleteDir('../'));
        $this->assertFalse($this->instance->deleteDir('/.'));
        $this->assertFalse($this->instance->deleteDir('/..'));
        $this->assertFalse($this->instance->deleteDir('/./'));
        $this->assertFalse($this->instance->deleteDir('/../'));

        $this->assertTrue(is_file($marker));

        unlink($marker);
    }

    public function testDeleteDirRefusesEmbeddedDotDot()
    {
        $baseDir = sys_get_temp_dir().'/filegen-'.uniqid();
        mkdir($baseDir, 0755, true);
        file_put_contents($baseDir.'/keep.txt', 'keep');

        $this->assertFalse($this->instance->deleteDir($baseDir.'/../'.basename($baseDir)));
        $this->assertFalse($this->instance->deleteDir($baseDir.'/sub/../../'.basename($baseDir)));

        $this->assertTrue(is_file($baseDir.'/keep.txt'));

        unlink($baseDir.'/keep.txt');
        rmdir($baseDir);
    }

    public function testSaveRefusesPlantedLink()
    {
        $baseDir = sys_get_temp_dir().'/filegen-'.uniqid();
        $outsideDir = sys_get_temp_dir().'/filegen-outside-'.uniqid();
        mkdir($baseDir, 0755, true);
        mkdir($outsideDir, 0755, true);
        symlink($outsideDir, $baseDir.'/linked');

        $this->instance->setProjectBaseDir($baseDir);

        $saved = $this->instance->save(['hello'], ['outputBaseDir' => 'linked', 'fileName' => 'Evil'], $this->console);

        $this->assertFalse($saved['created']);
        $this->assertFalse(is_file($outsideDir.'/Evil.php'));
        $this->assertFalse(is_file($baseDir.'/linked/Evil.php'));

        unlink($baseDir.'/linked');
        rmdir($outsideDir);
        rmdir($baseDir);
    }

    public function testSaveRefusesSlashBaseWithoutTouch()
    {
        $property = new \ReflectionProperty(FileGenerator::class, 'projectBaseDir');
        $property->setAccessible(true);
        $property->setValue($this->instance, '/');

        $dirName = 'should-not-create-'.uniqid();
        $saved = $this->instance->save(['hello'], ['outputBaseDir' => $dirName, 'fileName' => 'Nope'], $this->console);

        $this->assertFalse($saved['created']);
        $this->assertFalse(file_exists($saved['completeFilePath']));
        $this->assertFalse(is_dir(dirname($saved['completeFilePath'])));
        $this->assertStringContainsString($dirName, $saved['completeFilePath']);
    }

    public function testSaveRefusesBaseSymlink()
    {
        $realBase = sys_get_temp_dir().'/filegen-real-'.uniqid();
        $outsideDir = sys_get_temp_dir().'/filegen-outside-'.uniqid();
        mkdir($realBase, 0755, true);
        mkdir($outsideDir, 0755, true);
        $linkBase = sys_get_temp_dir().'/filegen-linkbase-'.uniqid();
        symlink($realBase, $linkBase);

        $this->instance->setProjectBaseDir($linkBase);

        $saved = $this->instance->save(['hello'], ['fileName' => 'BaseLinkEvil'], $this->console);

        $this->assertFalse($saved['created']);
        $this->assertFalse(is_file($realBase.'/BaseLinkEvil.php'));
        $this->assertFalse(is_file($linkBase.'/BaseLinkEvil.php'));

        unlink($linkBase);
        rmdir($outsideDir);
        rmdir($realBase);
    }

    public function testSaveRefusesBackslashAndNonStringInputs()
    {
        $baseDir = sys_get_temp_dir().'/filegen-'.uniqid();
        mkdir($baseDir, 0755, true);
        $this->instance->setProjectBaseDir($baseDir);

        $before = scandir($baseDir);

        $backslashDir = $this->instance->save(['hello'], ['outputBaseDir' => 'sub\\evil', 'fileName' => 'Nope'], $this->console);
        $this->assertFalse($backslashDir['created']);

        $backslashName = $this->instance->save(['hello'], ['fileName' => 'evil\\name'], $this->console);
        $this->assertFalse($backslashName['created']);

        $nonStringDir = $this->instance->save(['hello'], ['outputBaseDir' => 123, 'fileName' => 'Nope'], $this->console);
        $this->assertFalse($nonStringDir['created']);

        $nonStringName = $this->instance->save(['hello'], ['fileName' => 123], $this->console);
        $this->assertFalse($nonStringName['created']);

        $this->assertEquals($before, scandir($baseDir));

        rmdir($baseDir);
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

    private function invokeWriteFile($path, $content)
    {
        $method = new \ReflectionMethod(FileGenerator::class, 'writeFile');
        $method->setAccessible(true);

        return $method->invoke($this->instance, $path, $content);
    }
}
