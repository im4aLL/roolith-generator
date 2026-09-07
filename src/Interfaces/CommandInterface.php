<?php

namespace Roolith\Generator\Interfaces;

use Roolith\Generator\Command;
use Roolith\Generator\Console;
use Roolith\Generator\FileGenerator;
use Roolith\Generator\FileParser;

/**
 * Contract for generator command handlers.
 */
interface CommandInterface
{
    /**
     * Describe this command for registration.
     *
     * @return array<string, mixed>
     */
    public function register(): array;

    /**
     * Execute this command with shared generator services.
     *
     * @param Command $command
     * @param Console $console
     * @param FileParser $fileParser
     * @param FileGenerator $fileGenerator
     * @return mixed
     */
    public function handle(Command $command, Console $console, FileParser $fileParser, FileGenerator $fileGenerator): mixed;
}
