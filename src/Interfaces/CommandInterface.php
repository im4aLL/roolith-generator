<?php

namespace Roolith\Interfaces;

use Roolith\Command;
use Roolith\Console;
use Roolith\FileGenerator;
use Roolith\FileParser;

interface CommandInterface
{
    public function register();

    public function handle(Command $command, Console $console, FileParser $fileParser, FileGenerator $fileGenerator);
}
