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
}