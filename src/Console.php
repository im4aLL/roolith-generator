<?php
namespace Roolith\Generator;


/**
 * Wraps CLI arguments and console output.
 */
class Console
{
    /**
     * Create console with optional raw arguments including script name.
     *
     * @param string[]|null $arguments
     */
    public function __construct(private ?array $arguments = null)
    {
    }

    /**
     * Set raw CLI arguments including script name.
     *
     * @param string[] $arguments
     * @return void
     */
    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    /**
     * Get CLI arguments without script name.
     *
     * @return string[]
     */
    public function getArguments(): array
    {
        $arguments = $this->arguments;

        if (isset($arguments)) {
            array_shift($arguments);

            return $arguments;
        }

        return [];
    }

    /**
     * Check whether any CLI argument exists.
     *
     * @return bool
     */
    public function hasArgument(): bool
    {
        return count($this->getArguments()) > 0;
    }

    /**
     * Write a message without trailing newline.
     *
     * @param string $message
     * @return void
     */
    public function output(string $message): void
    {
        echo $message;
    }

    /**
     * Write a message with trailing newline.
     *
     * @param string $message
     * @return void
     */
    public function outputLine(string $message): void
    {
        $this->output($message);
        $this->outputNewLine();
    }

    /**
     * Write a newline.
     *
     * @return void
     */
    public function outputNewLine(): void
    {
        echo PHP_EOL;
    }
}
