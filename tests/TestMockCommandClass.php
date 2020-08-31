<?php
use Roolith\Generator\Command;
use Roolith\Generator\Console;
use Roolith\Generator\FileGenerator;
use Roolith\Generator\FileParser;
use Roolith\Generator\Interfaces\CommandInterface;

class TestMockCommandClass implements CommandInterface
{
    public function register()
    {
        return [
            'name' => 'generate',
            'alias' => [
                'g'
            ],
            'typeAlias' => [
                'controller' => ['c'],
                'command' => ['cmd'],
            ],
        ];
    }

    public function handle(Command $command, Console $console, FileParser $fileParser, FileGenerator $fileGenerator)
    {
        $console->output('Test command registered!');
    }
}