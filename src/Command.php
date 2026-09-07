<?php
namespace Roolith\Generator;


/**
 * Holds CLI arguments and registered command definitions.
 */
class Command
{
    /**
     * CLI arguments without script name.
     *
     * @var string[]
     */
    private array $arguments;

    /**
     * Registered command definitions.
     *
     * @var array<int, array<string, mixed>>
     */
    private array $registry;

    /**
     * Initialize empty arguments and registry.
     */
    public function __construct()
    {
        $this->arguments = [];
        $this->registry = [];
    }

    /**
     * Set CLI arguments for this command.
     *
     * @param string[] $arguments
     * @return self
     */
    public function bootstrap(array $arguments): self
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Get command name from first argument.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        $value = $this->getArgumentValueByIndex(0);

        return is_string($value) ? $value : null;
    }

    /**
     * Get command type from second argument, resolving type aliases.
     *
     * @return string|null
     */
    public function type(): ?string
    {
        $type = $this->getArgumentValueByIndex(1);

        if (!is_string($type)) {
            return null;
        }

        $command = $this->getRegisteredCommandByName($this->name());

        if (isset($command['typeAlias']) && is_array($command['typeAlias'])) {
            foreach ($command['typeAlias'] as $aliasKey => $aliasValueArray) {
                if (is_array($aliasValueArray) && in_array($type, $aliasValueArray, true)) {
                    return $aliasKey;
                }
            }
        }

        return $type;
    }

    /**
     * Get command value from third argument.
     *
     * @return string|null
     */
    public function value(): ?string
    {
        $value = $this->getArgumentValueByIndex(2);

        return is_string($value) ? $value : null;
    }

    /**
     * Get raw argument value by position.
     *
     * @param int $index
     * @return mixed
     */
    private function getArgumentValueByIndex(int $index): mixed
    {
        return $this->arguments[$index] ?? null;
    }

    /**
     * Get all registered command definitions.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRegistry(): array
    {
        return $this->registry;
    }

    /**
     * Register a command definition.
     *
     * @param array<string, mixed> $registry
     * @return void
     */
    public function register(array $registry): void
    {
        $this->registry[] = $registry;
    }

    /**
     * Find a registered command by name or alias.
     *
     * @param string|null $name
     * @return array<string, mixed>|null
     */
    public function getRegisteredCommandByName(?string $name): ?array
    {
        if ($name === null || $name === '') {
            return null;
        }

        foreach ($this->getRegistry() as $command) {
            if (!is_array($command)) {
                continue;
            }

            if (isset($command['name']) && $command['name'] === $name) {
                return $command;
            }

            if (!empty($command['alias'])) {
                if (is_string($command['alias']) && $command['alias'] === $name) {
                    return $command;
                }

                if (is_array($command['alias']) && in_array($name, $command['alias'], true)) {
                    return $command;
                }
            }
        }

        return null;
    }
}
