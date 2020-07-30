<?php
namespace Roolith;

use Roolith\Commands\GenerateCommand;

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
        foreach ($commandClassArray as $commandClass) {
            $classInstance = new $commandClass();
            $registrationArray = $classInstance->register();
            $registrationArray['instance'] = $classInstance;

            $this->command->register($registrationArray);
        }

        return $this;
    }
}
