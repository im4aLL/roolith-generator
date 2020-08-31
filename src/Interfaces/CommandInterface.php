<?php

namespace Roolith\Generator\Interfaces;

use Roolith\Generator\Command;
use Roolith\Generator\Console;
use Roolith\Generator\FileGenerator;
use Roolith\Generator\FileParser;

interface CommandInterface
{
    public function register();

    public function handle(Command $command, Console $console, FileParser $fileParser, FileGenerator $fileGenerator);
}
