<?php
namespace Roolith;


class Console
{
    private $arguments;
    private $consoleColor;

    public function __construct(ConsoleColor $consoleColor = null)
    {
        $this->arguments = null;
        $this->consoleColor = $consoleColor ? $consoleColor : new ConsoleColor();
    }

    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
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
        $this->outputNewLine();
        $this->output($message, $color);
    }

    public function outputNewLine()
    {
        echo PHP_EOL;
    }
}
