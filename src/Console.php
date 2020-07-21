<?php
namespace Roolith;


class Console
{
    private $arguments;
    private $command;
    private $commandType;
    private $commandValue;

    public function __construct($arguments)
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
}