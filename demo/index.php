<?php
use Roolith\Generator\GeneratorFactory;

require_once __DIR__ . '/../vendor/autoload.php';
require __DIR__. '/Commands/TestCommand.php';

$generator = GeneratorFactory::getInstance();
$generator
    ->setTemplateDirectory(__DIR__.'/template')
    ->setProjectBaseDirectory(__DIR__)
    ->registerCommandClass([
        TestCommand::class
    ])
    ->watch($argv);
