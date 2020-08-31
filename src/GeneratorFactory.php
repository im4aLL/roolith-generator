<?php
namespace Roolith\Generator;

class GeneratorFactory
{
    private static $instance = null;

    private function __construct()
    {
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
