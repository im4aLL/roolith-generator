<?php
namespace Roolith;


class Command
{
    private $arguments;

    public function __construct()
    {
        $this->arguments = [];
    }

    public function bootstrap($arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function name()
    {
        return $this->getArgumentValueByIndex(0);
    }

    public function type()
    {
        return $this->getArgumentValueByIndex(1);
    }

    public function value()
    {
        return $this->getArgumentValueByIndex(2);
    }

    private function getArgumentValueByIndex($index)
    {
        return isset($this->arguments[$index]) ? $this->arguments[$index] : null;
    }
}