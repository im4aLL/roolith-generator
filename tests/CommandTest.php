<?php
require_once __DIR__. '/TestMockCommandClass.php';

use PHPUnit\Framework\TestCase;
use Roolith\Generator\Command;

class CommandTest extends TestCase
{
    public $command;

    public function setUp(): void
    {
        $this->command = new Command();
    }

    public function testShouldGetArgumentName()
    {
        $this->command->bootstrap(['generate', 'controller', 'Demo']);
        $this->assertEquals('generate', $this->command->name());
    }

    public function testShouldGetArgumentType()
    {
        $this->command->bootstrap(['generate', 'controller', 'Demo']);
        $this->assertEquals('controller', $this->command->type());
    }

    public function testShouldGetArgumentValue()
    {
        $this->command->bootstrap(['generate', 'controller', 'Demo']);
        $this->assertEquals('Demo', $this->command->value());
    }

    public function testShouldGetArgumentTypeWithAlias()
    {
        $this->command->bootstrap(['generate', 'c', 'Demo']);

        $classInstance = new TestMockCommandClass();
        $registrationArray = $classInstance->register();
        $registrationArray['instance'] = $classInstance;

        $this->command->register($registrationArray);
        $this->assertEquals('controller', $this->command->type());
    }

    public function testShouldGetRegisteredCommandByName()
    {
        $this->command->bootstrap(['g', 'c', 'Demo']);

        $classInstance = new TestMockCommandClass();
        $registrationArray = $classInstance->register();
        $registrationArray['instance'] = $classInstance;

        $this->command->register($registrationArray);
        $this->assertEquals('generate', $this->command->getRegisteredCommandByName('g')['name']);
    }

    public function testShouldReturnRawTypeWhenCommandHasNoTypeAlias()
    {
        $this->command->bootstrap(['generate', 'controller', 'Demo']);
        $this->command->register(['name' => 'generate']);
        $this->assertEquals('controller', $this->command->type());
    }

    public function testShouldResolveTypeViaCommandAlias()
    {
        $this->command->bootstrap(['g', 'c', 'Demo']);

        $classInstance = new TestMockCommandClass();
        $registrationArray = $classInstance->register();
        $registrationArray['instance'] = $classInstance;

        $this->command->register($registrationArray);
        $this->assertEquals('controller', $this->command->type());
    }

    public function testShouldReturnNullWhenRegistryEntryMissesNameAndAlias()
    {
        $this->command->register(['foo' => 'bar']);
        $this->assertNull($this->command->getRegisteredCommandByName('x'));
    }

    public function testShouldIgnoreRegistryEntryWithoutAliasKey()
    {
        $this->command->register(['name' => 'generate']);
        $this->assertNull($this->command->getRegisteredCommandByName('missing'));
        $this->assertEquals('generate', $this->command->getRegisteredCommandByName('generate')['name']);
    }

    public function testShouldReturnRawTypeWhenTypeAliasIsScalar()
    {
        $this->command->bootstrap(['generate', 'c', 'Demo']);
        $this->command->register(['name' => 'generate', 'alias' => ['g'], 'typeAlias' => 'controller']);
        $this->assertEquals('c', $this->command->type());
    }

    public function testShouldReturnRawTypeWhenTypeAliasValueIsScalar()
    {
        $this->command->bootstrap(['generate', 'c', 'Demo']);
        $this->command->register(['name' => 'generate', 'alias' => ['g'], 'typeAlias' => ['controller' => 'c']]);
        $this->assertEquals('c', $this->command->type());
    }

    public function testShouldFindCommandByStringAlias()
    {
        $this->command->register(['name' => 'generate', 'alias' => 'g']);
        $this->assertEquals('generate', $this->command->getRegisteredCommandByName('g')['name']);
    }

    public function testShouldReturnRawTypeWhenNoTypeAliasMatches()
    {
        $this->command->bootstrap(['generate', 'service', 'Demo']);

        $classInstance = new TestMockCommandClass();
        $registrationArray = $classInstance->register();
        $registrationArray['instance'] = $classInstance;

        $this->command->register($registrationArray);
        $this->assertEquals('service', $this->command->type());
    }
}
