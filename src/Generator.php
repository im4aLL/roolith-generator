<?php
namespace Roolith\Generator;

use InvalidArgumentException;
use ReflectionClass;
use Roolith\Generator\Commands\GenerateCommand;
use Roolith\Generator\Interfaces\CommandInterface;

class Generator
{
    protected $console;
    protected $fileParser;
    protected $command;
    protected $fileGenerator;

    public $defaultCommandClass = [
        GenerateCommand::class
    ];

    public function __construct(Console $console, FileParser $fileParser, Command $command, FileGenerator $fileGenerator)
    {
        $this->console = $console;
        $this->fileParser = $fileParser;
        $this->command = $command;
        $this->fileGenerator = $fileGenerator;

        if (is_array($this->defaultCommandClass) && count($this->defaultCommandClass) > 0) {
            $this->registerCommandClass($this->defaultCommandClass);
        }
    }

    public function setTemplateDirectory($directory)
    {
        $this->fileParser->setDirectory($directory);

        return $this;
    }

    public function setProjectBaseDirectory($directory)
    {
        $this->fileGenerator->setProjectBaseDir($directory);

        return $this;
    }

    public function watch($arguments)
    {
        $this->console->setArguments($arguments);

        if ($this->console->hasArgument()) {
            $this->command->bootstrap($this->console->getArguments());

            $command = $this->command->getRegisteredCommandByName($this->command->name());
            if ($command) {
                $command['instance']->handle($this->command, $this->console, $this->fileParser, $this->fileGenerator);
            } else {
                $this->console->output("Command doesn't exists!");
            }
        }

        return $this;
    }

    public function registerCommandClass($commandClassArray)
    {
        if (!is_array($commandClassArray)) {
            throw new InvalidArgumentException("Command class list must be an array.");
        }

        foreach ($commandClassArray as $commandClass) {
            if (!is_string($commandClass) || (!class_exists($commandClass) && !interface_exists($commandClass))) {
                throw new InvalidArgumentException("Command class '".(is_string($commandClass) ? $commandClass : gettype($commandClass))."' does not exist.");
            }

            if (!is_subclass_of($commandClass, CommandInterface::class, true)) {
                throw new InvalidArgumentException("Command class '".$commandClass."' must implement CommandInterface.");
            }

            $reflection = new ReflectionClass($commandClass);

            if (!$reflection->isInstantiable()) {
                throw new InvalidArgumentException("Command class '".$commandClass."' must be instantiable.");
            }

            $constructor = $reflection->getConstructor();

            if ($constructor !== null && $constructor->getNumberOfRequiredParameters() > 0) {
                throw new InvalidArgumentException("Command class '".$commandClass."' must have no required constructor parameters.");
            }

            $classInstance = $reflection->newInstance();

            $registrationArray = $classInstance->register();
            $registrationArray['instance'] = $classInstance;

            $this->command->register($registrationArray);
        }

        return $this;
    }
}
