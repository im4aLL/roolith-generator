<?php
namespace Roolith;

use Roolith\Constants\ColorConstants;

class Generator
{
    private $console;
    private $fileParser;
    private $command;
    private $fileGenerator;

    public function __construct(Console $console, FileParser $fileParser, Command $command, FileGenerator $fileGenerator)
    {
        $this->console = $console;
        $this->fileParser = $fileParser;
        $this->command = $command;
        $this->fileGenerator = $fileGenerator;
    }

    public function watch()
    {
        if ($this->console->hasArgument()) {
            $this->command->bootstrap($this->console->getArguments());

            switch ($this->command->name()) {
                case 'generate':
                    $this->generateFile($this->command->type(), $this->command->value());
                    break;
            }
        }
    }

    public function setTemplateDirectory($directory)
    {
        $this->fileParser->setDirectory($directory);

        return $this;
    }

    public function setProjectBaseDirectory($directory)
    {
        $this->fileGenerator->setProjectBaseDir($directory);

        return $this;
    }

    protected function generateFile($type, $value)
    {
        if ($this->fileParser->templateExists($type)) {
            $parsedTemplateData = $this->fileParser->parseTemplate($type, $value);

            $saved = $this->fileGenerator->save($parsedTemplateData['lines'], $parsedTemplateData['instructions'], $this->console);

            if ($saved['created']) {
                $this->console->output($saved['filename'].' has been created!', ColorConstants::GREEN);
                $this->console->outputLine('Location: '.$saved['completeFilePath']);
            } else {
                $this->console->output('Unable to create file!', ColorConstants::RED);
            }
        }

        return $this;
    }
}
