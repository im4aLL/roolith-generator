<?php
namespace Roolith\Generator;

use Roolith\Generator\Constants\FileConstants;

class FileParser
{
    private $config;
    private $directory;
    private $instructions;

    public function __construct($config = ['extension' => 'txt', 'instructionPrefix' => '#'], $instructions = [])
    {
        $this->config = $config;
        $this->directory = null;
        $this->instructions = $instructions;

        $this->addInstruction(['name' => FileConstants::OUTPUT_BASE_DIR, 'match' => '# outputBaseDir:']);
    }

    public function setFileExtension($extension)
    {
        $this->config['extension'] = $extension;

        return $this;
    }

    public function getExtension()
    {
        return $this->config['extension'];
    }

    public function templateExists($name)
    {
        return file_exists($this->getFilePathByName($name));
    }

    private function getFilePathByName($name)
    {
        return $this->getDirectory().'/'.$name.'.'.$this->getExtension();
    }

    public function parseTemplate($type, $value)
    {
        $filename = $this->getFilePathByName($type);
        $fp = fopen($filename, 'r');

        $content = fread($fp, filesize($filename));
        $lines = explode("\n", $content);
        fclose($fp);

        return $this->bindValue($lines, $value, $type);
    }

    private function bindValue($lines, $value, $type)
    {
        $result = [];
        $instructions = [
            FileConstants::FILE_NAME => $this->titleCase($value).$this->titleCase($type),
        ];

        foreach ($lines as $line) {
            if (substr($line, 0, 1) === $this->config['instructionPrefix']) {
                $instruction = $this->extractInstructionFromLine($line);

                if (count($instruction) > 0) {
                    $instructions[$instruction['name']] = $instruction['value'];
                }
            } else {
                $result[] = $this->applyValueToLine($line, $value);
            }
        }

        return [
            'instructions' => $instructions,
            'lines' => $result,
        ];
    }

    private function titleCase($string)
    {
        return ucfirst($string);
    }

    private function applyValueToLine($line, $value)
    {
        $titleCaseValue = $this->titleCase($value);

        $patternArray = [
            '/{{name}}/',
            '/{name}/',
        ];

        $replacementArray = [
            $titleCaseValue,
            $value,
        ];

        return preg_replace($patternArray, $replacementArray, $line);
    }

    private function extractInstructionFromLine($line)
    {
        foreach ($this->instructions as $instruction) {
            preg_match('/^'.$instruction['match'].'/', $line, $matches);

            if (count($matches) > 0) {
                return [
                    'name' => $instruction['name'],
                    'value' => trim(str_replace($instruction['match'], '', $line)),
                ];
            }
        }

        return [];
    }

    public function getInstructions()
    {
        return $this->instructions;
    }

    public function addInstruction($instruction)
    {
        $this->instructions[] = $instruction;

        return $this;
    }

    public function setDirectory($directory)
    {
        $this->directory = $directory;

        return $this;
    }

    public function getDirectory()
    {
        return $this->directory;
    }
}
