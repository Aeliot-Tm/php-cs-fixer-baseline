<?php

declare(strict_types=1);

/*
 * This file is part of the box project.
 *
 * (c) Anatoliy Melnikov <5785276@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Aeliot\PhpCsFixerBaseline\Service;

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

class ConsoleOptionsReader
{
    private string $rootDirectory;

    /** @var array<string, string>  */
    private array $option;

    private static ?self $instance = null;

    public function __construct()
    {
        self::$instance = $this;
        $this->option = getopt('ab:c:d:f:w:', ['absolute', 'baseline:', 'config:', 'dir:', 'finder:', 'workdir:']);
        $this->rootDirectory = $this->option['d'] ?? $this->options['dir'] ?? '';
    }

    public static function getInstance(): self
    {
        return self::$instance ?: throw new \LogicException('No instance provided');
    }

    public function getAsArray(): array
    {
        return [
            'baselinePath' => $this->getBaselinePath(),
            'config' => $this->getConfig(),
            'finder' => $this->getFinder(),
            'relative' => $this->getRelative(),
            'workdir' => $this->getWorkdir(),
        ];
    }

    private function getBaselinePath(): string
    {
        return $this->getAbsolutePath($this->getOptionValue('b', 'baseline', '.php-cs-fixer-baseline.json'));
    }

    private function getConfig(): Config
    {
        $configPath = $this->getAbsolutePath($this->getOptionValue('c', 'config', '.php-cs-fixer.dist.php'));

        return require $configPath;
    }

    private function getFinder(): Finder
    {
        $finderPath = $this->getAbsolutePath($this->getOptionValue('f', 'finder', '.php-cs-fixer-finder.php'));

        return require $finderPath;
    }

    private function getWorkdir(): ?string
    {
        return $this->getOptionValue('w', 'workdir', null);
    }

    private function getRelative(): ?bool
    {
        return !$this->getOptionValue('a', 'relative', false);
    }

    private function getAbsolutePath(string $path): string
    {
        $path = $this->rootDirectory . $path;

        if (preg_match('#^(?:[[:alpha:]]:[/\\\\]|/)#', $path)) {
            return $path;
        }

        return getcwd() . '/' . $path;
    }

    private function getOptionValue(string $short, string $long, bool|null|string $default): bool|null|string
    {
        if (\array_key_exists($short, $this->option) && \array_key_exists($long, $this->option)) {
            throw new \InvalidArgumentException(sprintf('%s is duplicated', $long));
        }

        return $this->option[$short] ?? $this->option[$long] ?? $default;
    }
}
