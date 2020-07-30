<?php
use Roolith\Command;
use Roolith\Console;
use Roolith\FileGenerator;
use Roolith\FileParser;
use Roolith\Interfaces\CommandInterface;

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