<?php
namespace Roolith;

class GeneratorFactory
{
    public static function getInstance()
    {
        $console = new Console();
        $fileParser = new FileParser();
        $command = new Command();
        $fileGenerator = new FileGenerator();

        return new Generator($console, $fileParser, $command, $fileGenerator);
    }
}
