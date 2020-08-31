<?php
namespace Roolith\Generator\Commands;

use Roolith\Generator\Command;
use Roolith\Generator\Console;
use Roolith\Generator\FileGenerator;
use Roolith\Generator\FileParser;
use Roolith\Generator\Interfaces\CommandInterface;

class GenerateCommand implements CommandInterface
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
        if ($fileParser->templateExists($command->type())) {
            $parsedTemplateData = $fileParser->parseTemplate($command->type(), $command->value());

            $console->outputNewLine();
            $saved = $fileGenerator->save($parsedTemplateData['lines'], $parsedTemplateData['instructions'], $console);

            if ($saved['created']) {
                $console->output($saved['filename'].' has been created!');
                $console->outputLine('Location: '.$saved['completeFilePath']);
            } else {
                $console->output('Unable to create file!');
            }

            $console->outputNewLine();
        }

        return $this;
    }
}
