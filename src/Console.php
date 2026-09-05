<?php
namespace Roolith\Generator;


class Console
{
    private $arguments;

    public function __construct()
    {
        $this->arguments = null;
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

    public function output($message)
    {
        echo $message;
    }

    public function outputLine($message)
    {
        $this->output($message);
        $this->outputNewLine();
    }

    public function outputNewLine()
    {
        echo PHP_EOL;
    }
}
