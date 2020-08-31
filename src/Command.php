<?php
namespace Roolith\Generator;


class Command
{
    private $arguments;
    private $registry;

    public function __construct()
    {
        $this->arguments = [];
        $this->registry = [];
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
        $type = $this->getArgumentValueByIndex(1);
        $command = $this->getRegisteredCommandByName($this->name());

        if ($command['typeAlias']) {
            foreach ($command['typeAlias'] as $aliasKey => $aliasValueArray) {
                if (in_array($type, $aliasValueArray)) {
                    return $aliasKey;
                }
            }
        }

        return $type;
    }

    public function value()
    {
        return $this->getArgumentValueByIndex(2);
    }

    private function getArgumentValueByIndex($index)
    {
        return isset($this->arguments[$index]) ? $this->arguments[$index] : null;
    }

    public function getRegistry()
    {
        return $this->registry;
    }

    public function register($registry)
    {
        $this->registry[] = $registry;
    }

    public function getRegisteredCommandByName($name)
    {
        foreach ($this->getRegistry() as $command) {
            if ($command['name'] === $name) {
                return $command;
            }

            if ($command['alias']) {
                $typeOfName = gettype($command['alias']);

                if ($typeOfName === 'string' && $command['alias'] === $name) {
                    return $command;
                }

                if ($typeOfName === 'array' && in_array($name, $command['alias'])) {
                    return $command;
                }
            }
        }

        return null;
    }
}
