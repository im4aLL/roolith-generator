<?php
namespace Roolith\Generator\Commands;

use Roolith\Generator\Command;
use Roolith\Generator\Console;
use Roolith\Generator\FileGenerator;
use Roolith\Generator\FileParser;
use Roolith\Generator\Interfaces\CommandInterface;

/**
 * Handles the generate command for scaffolding files from templates.
 */
class GenerateCommand implements CommandInterface
{
    /**
     * Describe this command for registration.
     *
     * @return array<string, mixed>
     */
    public function register(): array
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

    /**
     * Generate a file from the requested template.
     *
     * @param Command $command
     * @param Console $console
     * @param FileParser $fileParser
     * @param FileGenerator $fileGenerator
     * @return mixed
     */
    public function handle(Command $command, Console $console, FileParser $fileParser, FileGenerator $fileGenerator): mixed
    {
        $type = $command->type();

        if ($type === null || $type === '') {
            $console->outputLine('Missing template type. Usage: generate <type> <Name>');

            return $this;
        }

        if (!$fileParser->templateExists($type)) {
            $console->outputLine("Template '".$type."' doesn't exist!");

            return $this;
        }

        $parsedTemplateData = $fileParser->parseTemplate($type, $command->value());

        if (!is_array($parsedTemplateData) || !isset($parsedTemplateData['lines'], $parsedTemplateData['instructions'])) {
            $console->outputLine('Unable to parse template!');

            return $this;
        }

        $console->outputNewLine();
        $saved = $fileGenerator->save($parsedTemplateData['lines'], $parsedTemplateData['instructions'], $console);

        if ($saved['created']) {
            $console->outputLine($saved['filename'].' has been created!');
            $console->outputLine('Location: '.$saved['completeFilePath']);
        } else {
            $console->outputLine('Unable to create file!');
        }

        $console->outputNewLine();

        return $this;
    }
}
