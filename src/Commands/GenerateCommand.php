<?php
namespace Roolith\Commands;

use Roolith\Command;
use Roolith\Console;
use Roolith\Constants\ColorConstants;
use Roolith\FileGenerator;
use Roolith\FileParser;
use Roolith\Interfaces\CommandInterface;

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
                $console->output($saved['filename'].' has been created!', ColorConstants::GREEN);
                $console->outputLine('Location: '.$saved['completeFilePath']);
            } else {
                $console->output('Unable to create file!', ColorConstants::RED);
            }

            $console->outputNewLine();
        }

        return $this;
    }
}
