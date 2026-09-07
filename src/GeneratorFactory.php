<?php
namespace Roolith\Generator;

class GeneratorFactory
{
    private static $instance = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public function __unserialize(array $data)
    {
        throw new \LogicException('Cannot unserialize GeneratorFactory singleton.');
    }

    /**
     * Resets the singleton instance. Intended for tests only.
     * @internal
     */
    public static function reset()
    {
        self::$instance = null;
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            $console = new Console();
            $fileParser = new FileParser();
            $command = new Command();
            $fileGenerator = new FileGenerator();

            self::$instance = new Generator($console, $fileParser, $command, $fileGenerator);
        }

        return self::$instance;
    }
}
