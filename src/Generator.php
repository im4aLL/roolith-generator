<?php
namespace Roolith\Generator;

use InvalidArgumentException;
use ReflectionClass;
use Roolith\Generator\Commands\GenerateCommand;
use Roolith\Generator\Interfaces\CommandInterface;

/**
 * Coordinates console input with registered command handlers.
 */
class Generator
{
    /**
     * Default command classes registered on construction.
     *
     * @var array<int, class-string<CommandInterface>>
     */
    public array $defaultCommandClass = [
        GenerateCommand::class
    ];

    /**
     * Create generator with console, parser, command, and file writer.
     *
     * @param Console $console
     * @param FileParser $fileParser
     * @param Command $command
     * @param FileGenerator $fileGenerator
     */
    public function __construct(
        protected Console $console,
        protected FileParser $fileParser,
        protected Command $command,
        protected FileGenerator $fileGenerator
    ) {
        if (count($this->defaultCommandClass) > 0) {
            $this->registerCommandClass($this->defaultCommandClass);
        }
    }

    /**
     * Set template directory path.
     *
     * @param string $directory
     * @return self
     */
    public function setTemplateDirectory(string $directory): self
    {
        $this->fileParser->setDirectory($directory);

        return $this;
    }

    /**
     * Set project base directory for generated files.
     *
     * @param string $directory
     * @return self
     */
    public function setProjectBaseDirectory(string $directory): self
    {
        $this->fileGenerator->setProjectBaseDir($directory);

        return $this;
    }

    /**
     * Dispatch raw CLI arguments to the matching command handler.
     *
     * @param string[] $arguments
     * @return self
     */
    public function watch(array $arguments): self
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

    /**
     * Register command classes that implement CommandInterface.
     *
     * @param mixed $commandClassArray Expects array<int, class-string<CommandInterface>>.
     * @return self
     * @throws InvalidArgumentException When the list or a command class is invalid.
     */
    public function registerCommandClass(mixed $commandClassArray): self
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
