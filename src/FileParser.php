<?php
namespace Roolith;

class FileParser
{
    private $config;
    private $directory;

    public function __construct($config = ['extension' => 'txt'])
    {
        $this->config = $config;
        $this->directory = null;
    }

    public function setDirectory($directory)
    {
        $this->directory = $directory;

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
        return $this->directory.'/'.$name.'.'.$this->getExtension();
    }

    public function parseTemplate($type, $value)
    {
        $filename = $this->getFilePathByName($type);
        $fp = fopen($filename, "r");

        $content = fread($fp, filesize($filename));
        $lines = explode("\n", $content);
        fclose($fp);

        return $this->bindValue($lines, $value);
    }

    private function bindValue($lines, $value)
    {
        
    }
}