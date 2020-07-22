<?php
use Roolith\GeneratorFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$generator = GeneratorFactory::getInstance();
$generator
    ->setTemplateDirectory(__DIR__.'/template')
    ->setProjectBaseDirectory(__DIR__)
    ->watch($argv);
