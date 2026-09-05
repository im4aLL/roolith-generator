<?php
namespace Roolith\Generator;

use Roolith\Generator\Constants\FileConstants;

/**
 * Generates files from parsed templates with filesystem safety guards.
 */
class FileGenerator
{
    /**
     * Generator config, expects extension key.
     *
     * @var array{extension?: string}
     */
    private array $config;

    /**
     * Project base directory; empty and root slash refused by setter.
     *
     * @var string
     */
    private string $projectBaseDir;

    /**
     * @param array{extension?: string} $config
     */
    public function __construct(array $config = ['extension' => 'php'])
    {
        $this->config = $config;

        $cwd = getcwd();

        if (!is_string($cwd) || $cwd === '' || $cwd === '/') {
            $cwd = '.';
        }

        $this->projectBaseDir = $cwd;
    }

    /**
     * Set output file extension.
     *
     * @param string $extension
     * @return self
     */
    public function setFileExtension(string $extension): self
    {
        $this->config['extension'] = $extension;

        return $this;
    }

    /**
     * Set project base directory, ignores empty, root slash, and non-string input.
     *
     * @param mixed $directory
     * @return self
     */
    public function setProjectBaseDir(mixed $directory): self
    {
        if (!is_string($directory) || $directory === '' || $directory === '/') {
            return $this;
        }

        $this->projectBaseDir = $directory;

        return $this;
    }

    /**
     * Save lines to file described by instructions.
     *
     * @param string[] $lines
     * @param mixed $instructions
     * @param Console $console
     * @return array{created: bool, completeFilePath: string, filename: string}
     */
    public function save(array $lines, mixed $instructions, Console $console): array
    {
        $content = implode("\n", $lines);
        $outputDir = $this->getOutputDirByInstructions($instructions);
        $fileName = $this->getOutputFileNameByInstruction($instructions);
        $completeFilePath = $outputDir.'/'.$fileName;

        if ($this->hasUnsafeInstructions($instructions)) {
            return [
                'created' => false,
                'completeFilePath' => $completeFilePath,
                'filename' => $fileName,
            ];
        }

        $normalizedBase = is_string($this->projectBaseDir) ? rtrim($this->projectBaseDir, '/') : '';

        if ($normalizedBase === '' || $normalizedBase === '/' || $normalizedBase === '.' || $normalizedBase === '..') {
            if ($normalizedBase !== '.') {
                return [
                    'created' => false,
                    'completeFilePath' => $completeFilePath,
                    'filename' => $fileName,
                ];
            }
        }

        $normalizedOutput = rtrim($outputDir, '/');

        while (strpos($normalizedOutput, '//') === 0) {
            $normalizedOutput = substr($normalizedOutput, 1);
        }

        if ($normalizedOutput === '' || $normalizedOutput === '/' || $normalizedOutput === '.' || $normalizedOutput === '..') {
            return [
                'created' => false,
                'completeFilePath' => $completeFilePath,
                'filename' => $fileName,
            ];
        }

        if (!is_string($this->projectBaseDir) || is_link($this->projectBaseDir) || is_link($outputDir) || is_link($completeFilePath) || $this->outputPathContainsLink($this->projectBaseDir, $outputDir) || $this->outputPathContainsLink($this->projectBaseDir, $completeFilePath)) {
            return [
                'created' => false,
                'completeFilePath' => $completeFilePath,
                'filename' => $fileName,
            ];
        }

        $shouldWrite = true;

        if (file_exists($completeFilePath)) {
            $console->output('The file '.$fileName.' already exists. Do you want to overwrite it? (yes/no) ');
            $shouldWrite = $this->getOverwriteConfirmation();
        }

        $saved = false;

        if ($shouldWrite) {
            $saved = $this->makeFolderIfDoesntExist($outputDir)->writeFile($completeFilePath, $content);
        }

        return [
            'created' => $saved,
            'completeFilePath' => $completeFilePath,
            'filename' => $fileName,
        ];
    }

    /**
     * Confirm overwrite from handle, closes handle when possible.
     *
     * @param mixed $handle Expects resource|null.
     * @return bool
     */
    private function getOverwriteConfirmation(mixed $handle = null): bool
    {
        if ($handle === null) {
            $handle = fopen('php://stdin', 'r');

            if ($handle === false) {
                return false;
            }
        }

        if (!is_resource($handle)) {
            return false;
        }

        $line = fgets($handle);

        if (is_resource($handle)) {
            fclose($handle);
        }

        if (!is_string($line)) {
            return false;
        }

        $answer = strtolower(trim($line));

        return $answer === 'yes' || $answer === 'y';
    }

    /**
     * Resolve output directory from instructions.
     *
     * @param mixed $instructions
     * @return string
     */
    private function getOutputDirByInstructions(mixed $instructions): string
    {
        $dirString = $this->projectBaseDir;

        if (is_array($instructions) && isset($instructions[FileConstants::OUTPUT_BASE_DIR]) && is_string($instructions[FileConstants::OUTPUT_BASE_DIR]) && $instructions[FileConstants::OUTPUT_BASE_DIR] !== '') {
            $dirString .= '/'.$instructions[FileConstants::OUTPUT_BASE_DIR];
        }

        return $dirString;
    }

    /**
     * Resolve output filename from instructions.
     *
     * @param mixed $instructions
     * @return string
     */
    private function getOutputFileNameByInstruction(mixed $instructions): string
    {
        $raw = (is_array($instructions) && isset($instructions[FileConstants::FILE_NAME])) ? $instructions[FileConstants::FILE_NAME] : '';
        $fileName = is_string($raw) ? $raw : '';
        $extension = $this->config['extension'] ?? 'php';

        return $fileName.'.'.$extension;
    }

    /**
     * Check instructions for absolute paths, traversal, and separators.
     *
     * @param mixed $instructions
     * @return bool True when unsafe and save must refuse.
     */
    private function hasUnsafeInstructions(mixed $instructions): bool
    {
        if (!is_array($instructions)) {
            return false;
        }

        if (isset($instructions[FileConstants::OUTPUT_BASE_DIR]) && $instructions[FileConstants::OUTPUT_BASE_DIR]) {
            $dir = $instructions[FileConstants::OUTPUT_BASE_DIR];

            if (!is_string($dir)) {
                return true;
            }

            if ($dir[0] === '/' || $dir[0] === '\\') {
                return true;
            }

            if (strpos($dir, '\\') !== false) {
                return true;
            }

            foreach (explode('/', $dir) as $part) {
                if ($part === '..') {
                    return true;
                }
            }
        }

        if (isset($instructions[FileConstants::FILE_NAME]) && $instructions[FileConstants::FILE_NAME] !== '') {
            $name = $instructions[FileConstants::FILE_NAME];

            if (!is_string($name)) {
                return true;
            }

            if (strpos($name, '/') !== false || strpos($name, '\\') !== false) {
                return true;
            }

            if ($name === '.' || $name === '..' || strpos($name, '..') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check whether path is inside a symlink scoped under base directory.
     *
     * @param mixed $baseDir
     * @param mixed $path
     * @return bool True when unsafe and save must refuse.
     */
    private function outputPathContainsLink(mixed $baseDir, mixed $path): bool
    {
        if (!is_string($baseDir) || !is_string($path)) {
            return true;
        }

        $normalizedBase = rtrim($baseDir, '/');

        if ($normalizedBase === '' || $normalizedBase === '/') {
            return true;
        }

        if ($path === '' || $path === '/') {
            return true;
        }

        if (is_link($baseDir)) {
            return true;
        }

        $base = rtrim($baseDir, '/');
        $target = rtrim($path, '/');

        while (strpos($target, '//') === 0) {
            $target = substr($target, 1);
        }

        if ($target !== $base && strpos($target, $base.'/') !== 0) {
            return true;
        }

        $relative = $target === $base ? '' : substr($target, strlen($base) + 1);

        if ($relative === '') {
            return is_link($target);
        }

        $current = $base;

        foreach (explode('/', str_replace('\\', '/', $relative)) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            $current .= '/'.$part;

            if (is_link($current)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create output directory when missing, refuses links and blocking files.
     *
     * @param mixed $outputDir
     * @return self
     */
    private function makeFolderIfDoesntExist(mixed $outputDir): self
    {
        if (!is_string($outputDir) || $outputDir === '' || $outputDir === '/') {
            return $this;
        }

        if (is_link($outputDir)) {
            return $this;
        }

        if (file_exists($outputDir) && !is_dir($outputDir)) {
            return $this;
        }

        if (is_dir($outputDir)) {
            return $this;
        }

        $parent = dirname($outputDir);

        if ($parent !== '' && $parent !== '.' && $parent !== '/' && file_exists($parent) && !is_dir($parent)) {
            return $this;
        }

        if ($parent !== '' && $parent !== '.' && !is_dir($parent) && file_exists($parent) === false) {
            $nearest = $parent;

            while ($nearest !== '' && $nearest !== '.' && $nearest !== '/' && !file_exists($nearest)) {
                $nearest = dirname($nearest);
            }

            if ($nearest !== '' && $nearest !== '.' && $nearest !== '/' && file_exists($nearest) && !is_dir($nearest)) {
                return $this;
            }
        }

        if (!is_dir($parent) && $parent !== '' && $parent !== '.' && $parent !== '/') {
            $this->makeFolderIfDoesntExist($parent);
        }

        if (!is_dir($outputDir)) {
            if (!is_writable(dirname($outputDir)) && is_dir(dirname($outputDir))) {
                return $this;
            }

            if (!mkdir($outputDir, 0755, true) && !is_dir($outputDir)) {
                return $this;
            }
        }

        return $this;
    }

    /**
     * Write content to file, returns false on failure.
     *
     * @param mixed $completeFilePath
     * @param string $content
     * @return bool
     */
    private function writeFile(mixed $completeFilePath, string $content): bool
    {
        if (!is_string($completeFilePath) || $completeFilePath === '' || $completeFilePath === '/') {
            return false;
        }

        if (is_link($completeFilePath)) {
            return false;
        }

        if (is_dir($completeFilePath)) {
            return false;
        }

        $parentDir = dirname($completeFilePath);

        if (is_link($parentDir)) {
            return false;
        }

        if ($parentDir !== '' && $parentDir !== '.' && !is_dir($parentDir)) {
            return false;
        }

        if (is_dir($parentDir) && !is_writable($parentDir)) {
            return false;
        }

        if (file_exists($completeFilePath) && !is_file($completeFilePath)) {
            return false;
        }

        $fp = fopen($completeFilePath, 'wb');

        if ($fp === false) {
            return false;
        }

        if (fwrite($fp, $content) === false) {
            fclose($fp);

            return false;
        }

        fclose($fp);

        return file_exists($completeFilePath);
    }

    /**
     * Get generator config.
     *
     * @return array{extension?: string}
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Recursively delete directory including dotfiles, unlinks symlinks without following.
     *
     * @param mixed $dirPath
     * @return bool True on full success, false when nothing deleted or partially deleted.
     */
    public function deleteDir(mixed $dirPath): bool
    {
        if (!is_string($dirPath)) {
            return false;
        }

        $normalized = rtrim(str_replace('\\', '/', $dirPath), '/');

        if ($normalized === '' || $normalized === '/' || $normalized === '.' || $normalized === '..') {
            return false;
        }

        $segments = explode('/', $normalized);
        $nonEmpty = [];

        foreach ($segments as $segment) {
            if ($segment !== '') {
                $nonEmpty[] = $segment;
            }
        }

        if (count($nonEmpty) === 0) {
            return false;
        }

        $allDot = true;

        foreach ($nonEmpty as $segment) {
            if ($segment !== '.') {
                $allDot = false;
                break;
            }
        }

        if ($allDot) {
            return false;
        }

        foreach ($nonEmpty as $segment) {
            if ($segment === '..') {
                return false;
            }
        }

        $dirPath = rtrim($dirPath, '/');

        if ($dirPath === '' || $dirPath === '/' || $dirPath === '.' || $dirPath === '..') {
            return false;
        }

        if (is_link($dirPath)) {
            if (!is_writable(dirname($dirPath)) && is_dir(dirname($dirPath))) {
                return false;
            }

            return unlink($dirPath);
        }

        if (!is_dir($dirPath)) {
            return false;
        }

        if (!is_readable($dirPath)) {
            return false;
        }

        $entries = scandir($dirPath);

        if ($entries === false) {
            return false;
        }

        $ok = true;

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $full = rtrim($dirPath, '/').'/'.$entry;

            if (is_link($full)) {
                if (!unlink($full)) {
                    $ok = false;
                }

                continue;
            }

            if (is_dir($full)) {
                if (!$this->deleteDir($full)) {
                    $ok = false;
                }

                continue;
            }

            if (is_file($full)) {
                if (!unlink($full)) {
                    $ok = false;
                }

                continue;
            }

            $ok = false;
        }

        if (!$ok) {
            $remaining = scandir($dirPath);

            if ($remaining === false) {
                return false;
            }

            foreach ($remaining as $entry) {
                if ($entry !== '.' && $entry !== '..') {
                    return false;
                }
            }
        }

        if (!is_dir($dirPath)) {
            return false;
        }

        if (!is_writable(dirname($dirPath)) && is_dir(dirname($dirPath))) {
            return false;
        }

        if (!rmdir($dirPath)) {
            return false;
        }

        return $ok;
    }
}
