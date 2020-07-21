<?php
namespace Roolith;

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

    protected function generateFile($type, $value)
    {
        if ($this->fileParser->templateExists($type)) {
            $this->fileParser->parseTemplate($type, $value);
        }
    }
}