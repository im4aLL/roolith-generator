<?php
namespace Roolith\Generator;

use Roolith\Generator\Constants\FileConstants;

/**
 * Locates and parses template files into lines and instructions.
 */
class FileParser
{
    /**
     * Parser config with extension and instruction prefix.
     *
     * @var array{extension?: string, instructionPrefix?: string}
     */
    private array $config;

    /**
     * Template directory path.
     *
     * @var string|null
     */
    private ?string $directory;

    /**
     * Registered template instructions.
     *
     * @var array<int, array{name?: string, match?: string}>
     */
    private array $instructions;

    /**
     * Create parser with default config and output base dir instruction.
     *
     * @param array{extension?: string, instructionPrefix?: string} $config
     * @param array<int, array{name?: string, match?: string}> $instructions
     */
    public function __construct(array $config = ['extension' => 'txt', 'instructionPrefix' => '#'], array $instructions = [])
    {
        $this->config = $config;
        $this->directory = null;
        $this->instructions = $instructions;

        $this->addInstruction(['name' => FileConstants::OUTPUT_BASE_DIR, 'match' => '# outputBaseDir:']);
    }

    /**
     * Set template file extension.
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
     * Get template file extension.
     *
     * @return string
     */
    public function getExtension(): string
    {
        return $this->config['extension'] ?? 'txt';
    }

    /**
     * Check whether a template file exists.
     *
     * @param string $name
     * @return bool
     */
    public function templateExists(string $name): bool
    {
        return is_file($this->getFilePathByName($name));
    }

    /**
     * Resolve template file path by name.
     *
     * @param string $name
     * @return string
     */
    private function getFilePathByName(string $name): string
    {
        return $this->getDirectory().'/'.$name.'.'.$this->getExtension();
    }

    /**
     * Parse a template into instructions and lines.
     *
     * @param string $type
     * @param string|null $value
     * @return array{instructions: array<string, string>, lines: string[]}|null
     */
    public function parseTemplate(string $type, ?string $value): ?array
    {
        $filename = $this->getFilePathByName($type);

        if (!is_file($filename) || !is_readable($filename)) {
            return null;
        }

        $content = file_get_contents($filename);

        if ($content === false) {
            return null;
        }

        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = explode("\n", $content);

        return $this->bindValue($lines, $value ?? '');
    }

    /**
     * Bind a value to template lines and collect instructions.
     *
     * @param string[] $lines
     * @param string $value
     * @return array{instructions: array<string, string>, lines: string[]}
     */
    private function bindValue(array $lines, string $value): array
    {
        $result = [];
        $instructions = [
            FileConstants::FILE_NAME => $this->titleCase($value),
        ];
        $prefix = $this->config['instructionPrefix'] ?? '#';

        foreach ($lines as $line) {
            if ($prefix !== '' && str_starts_with($line, $prefix)) {
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

    /**
     * Uppercase the first character of a string.
     *
     * @param string|null $string
     * @return string
     */
    private function titleCase(?string $string): string
    {
        return ucfirst($string ?? '');
    }

    /**
     * Replace name placeholders in a template line.
     *
     * @param string $line
     * @param string|null $value
     * @return string
     */
    private function applyValueToLine(string $line, ?string $value): string
    {
        $raw = $value ?? '';
        $titleCaseValue = $this->titleCase($raw);

        return strtr($line, ['{{name}}' => $titleCaseValue, '{name}' => $raw]);
    }

    /**
     * Extract a registered instruction from a template line.
     *
     * @param string $line
     * @return array{name: string, value: string}|array{}
     */
    private function extractInstructionFromLine(string $line): array
    {
        foreach ($this->instructions as $instruction) {
            if (!is_array($instruction) || !isset($instruction['match'], $instruction['name'])) {
                continue;
            }

            $match = $instruction['match'];

            if (!is_string($match) || $match === '' || !str_starts_with($line, $match)) {
                continue;
            }

            return [
                'name' => $instruction['name'],
                'value' => trim(str_replace($match, '', $line)),
            ];
        }

        return [];
    }

    /**
     * Get all registered instructions.
     *
     * @return array<int, array{name?: string, match?: string}>
     */
    public function getInstructions(): array
    {
        return $this->instructions;
    }

    /**
     * Register a template instruction matcher.
     *
     * @param array{name?: string, match?: string} $instruction
     * @return self
     */
    public function addInstruction(array $instruction): self
    {
        $this->instructions[] = $instruction;

        return $this;
    }

    /**
     * Set template directory path.
     *
     * @param string|null $directory
     * @return self
     */
    public function setDirectory(?string $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Get template directory path.
     *
     * @return string|null
     */
    public function getDirectory(): ?string
    {
        return $this->directory;
    }
}
