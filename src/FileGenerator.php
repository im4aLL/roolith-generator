<?php
namespace Roolith\Generator;

use Roolith\Generator\Constants\FileConstants;

class FileGenerator
{
    private $config;
    private $projectBaseDir;

    public function __construct($config = ['extension' => 'php'])
    {
        $this->config = $config;
        $this->projectBaseDir = '/';
    }

    public function setFileExtension($extension)
    {
        $this->config['extension'] = $extension;

        return $this;
    }

    public function setProjectBaseDir($directory)
    {
        $this->projectBaseDir = $directory;

        return $this;
    }

    public function save($lines, $instructions, Console $console)
    {
        $content = implode($lines);
        $outputDir = $this->getOutputDirByInstructions($instructions);
        $fileName = $this->getOutputFileNameByInstruction($instructions);
        $completeFilePath = $outputDir.'/'.$fileName;

        if (file_exists($completeFilePath)) {
            $console->output('The file '.$fileName.' already exists. Do you want to overwrite it? (yes/no) ');
            $confirmed = $this->getOverwriteConfirmation();

            if ($confirmed) {
                $saved = $this->makeFolderIfDoesntExist($outputDir)->writeFile($completeFilePath, $content);
            }
        } else {
            $saved = $this->makeFolderIfDoesntExist($outputDir)->writeFile($completeFilePath, $content);
        }

        return [
            'created' => isset($saved) ? $saved : false,
            'completeFilePath' => $completeFilePath,
            'filename' => $fileName,
        ];
    }

    private function getOverwriteConfirmation()
    {
        $handle = fopen('php://stdin', 'r');
        $line = fgets($handle);

        if(trim($line) === 'yes' || trim($line) === 'y'){
            return true;
        }

        fclose($handle);

        return false;
    }

    private function getOutputDirByInstructions($instructions)
    {
        $dirString = $this->projectBaseDir;

        if (count($instructions) > 0) {
            if ($instructions[FileConstants::OUTPUT_BASE_DIR]) {
                $dirString .= '/'.$instructions[FileConstants::OUTPUT_BASE_DIR];
            }
        }

        return $dirString;
    }

    private function getOutputFileNameByInstruction($instructions)
    {
        return $instructions[FileConstants::FILE_NAME].'.'.$this->config['extension'];
    }

    private function makeFolderIfDoesntExist($outputDir)
    {
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        return $this;
    }

    private function writeFile($completeFilePath, $content)
    {
        $fp = fopen($completeFilePath, 'wb');
        fwrite($fp, $content);
        fclose($fp);

        return file_exists($completeFilePath);
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function deleteDir($dirPath)
    {
        if (substr($dirPath, strlen($dirPath) - 1, 1) !== '/') {
            $dirPath .= '/';
        }

        $files = glob($dirPath . '*', GLOB_MARK);

        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDir($file);
            } else {
                unlink($file);
            }
        }

        rmdir($dirPath);
    }
}
