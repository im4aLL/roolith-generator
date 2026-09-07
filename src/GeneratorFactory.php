<?php
namespace Roolith\Generator;

/**
 * Singleton factory for the default Generator instance.
 */
class GeneratorFactory
{
    /**
     * Shared generator instance.
     *
     * @var Generator|null
     */
    private static ?Generator $instance = null;

    /**
     * Prevent direct construction.
     */
    private function __construct()
    {
    }

    /**
     * Prevent cloning.
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization.
     *
     * @return void
     * @throws \LogicException Always.
     */
    public function __wakeup(): void
    {
        throw new \LogicException('Cannot unserialize GeneratorFactory singleton.');
    }

    /**
     * Prevent unserialization from array data.
     *
     * @param array<mixed> $data
     * @return void
     * @throws \LogicException Always.
     */
    public function __unserialize(array $data): void
    {
        throw new \LogicException('Cannot unserialize GeneratorFactory singleton.');
    }

    /**
     * Resets the singleton instance. Intended for tests only.
     *
     * @return void
     * @internal
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * Get the shared generator instance, creating it once.
     *
     * @return Generator
     */
    public static function getInstance(): Generator
    {
        if (!self::$instance) {
            $console = new Console();
            $fileParser = new FileParser();
            $command = new Command();
            $fileGenerator = new FileGenerator();

            self::$instance = new Generator($console, $fileParser, $command, $fileGenerator);
        }

        return self::$instance;
    }
}
