<?php
namespace Roolith;


class Console
{
    private $arguments;
    private $consoleColor;

    public function __construct($arguments, ConsoleColor $consoleColor = null)
    {
        $this->arguments = $arguments;
        $this->consoleColor = $consoleColor ? $consoleColor : new ConsoleColor();
    }

    public function getArguments()
    {
        $arguments = $this->arguments;

        if (isset($arguments)) {
            array_shift($arguments);

            return $arguments;
        }

        return [];
    }

    public function hasArgument()
    {
        return count($this->getArguments()) > 0;
    }

    public function output($message, $color = null)
    {
        if ($color) {
            echo $this->consoleColor->getColoredString($message, $color);
        } else {
            echo $message;
        }
    }

    public function outputLine($message, $color = null)
    {
        echo PHP_EOL;
        $this->output($message, $color);
    }
}
