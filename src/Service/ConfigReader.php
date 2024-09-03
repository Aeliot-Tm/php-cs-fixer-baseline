<?php

declare(strict_types=1);

namespace Aeliot\PhpCsFixerBaseline\Service;

use InvalidArgumentException;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

class ConfigReader
{
    private string $rootDirectory;
    private array $option;

    private static self $instance;

    public function __construct()
    {
        static::$instance = $this;
        $this->option = getopt('b:c:f:d:', ['baseline:', 'config:', 'finder:', 'dir:']);
        $this->rootDirectory = $this->option['d'] ?? $this->options['dir'] ?? '';
    }

    public function getBaselinePath(): string
    {
        return $this->getAbsolutePath($this->getOptionValue('b', 'baseline', '.php-cs-fixer-baseline.json'));
    }

    public function getConfig(): Config
    {
        $configPath = $this->getAbsolutePath($this->getOptionValue('c', 'config', '.php-cs-fixer.dist.php'));

        return require $configPath;
    }

    public function getFinder(): Finder
    {
        $finderPath = $this->getAbsolutePath($this->getOptionValue('f', 'finder', '.php-cs-fixer-finder.php'));

        return require $finderPath;
    }

    public static function getInstance(): self
    {
        return self::$instance ?: throw new \LogicException('No instance provided');
    }

    private function getAbsolutePath(string $path): string
    {
        $path = $this->rootDirectory . $path;

        if (preg_match('#^(?:[[:alpha:]]:[/\\\\]|/)#', $path)) {
            return $path;
        }

        return getcwd() . '/' . $path;
    }

    private function getOptionValue(string $short, string $long, string $default): string
    {
        if (array_key_exists($short, $this->option) && array_key_exists($long, $this->option)) {
            throw new InvalidArgumentException(sprintf('%s is duplicated', $long));
        }

        return $this->option[$short] ?? $this->option[$long] ?? $default;
    }
}
