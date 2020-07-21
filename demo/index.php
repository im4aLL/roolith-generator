<?php

use Roolith\Command;
use Roolith\Console;
use Roolith\FileGenerator;
use Roolith\FileParser;
use Roolith\Generator;

require_once __DIR__ . '/../vendor/autoload.php';

$console = new Console($argv);
$fileParser = new FileParser();
$command = new Command();
$fileGenerator = new FileGenerator();
$generator = new Generator($console, $fileParser, $command, $fileGenerator);
$generator->setTemplateDirectory(__DIR__.'/template')->watch();