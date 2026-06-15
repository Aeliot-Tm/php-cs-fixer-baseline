<?php

declare(strict_types=1);

/*
 * This file is part of the PHP CS Fixer Baseline project.
 *
 * (c) Anatoliy Melnikov <5785276@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Aeliot\PhpCsFixerBaseline\Service;

use Aeliot\PhpCsFixerBaseline\Exception\InvalidArgumentException;
use Aeliot\PhpCsFixerBaseline\Model\BuilderConfig;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use Symfony\Component\Console\Input\InputInterface;

final class BuilderConfigFactory
{
    private const DEFAULT_FIXER_CONFIG_FILES = ['.php-cs-fixer.dist.php', '.php-cs-fixer.php'];

    public function createFromInput(InputInterface $input): BuilderConfig
    {
        $rootDirectory = $this->getStringOption($input, 'config-dir', '');
        $configPath = $this->resolveFixerPath($rootDirectory, $input);
        $baselineOptions = $this->resolveBaselineOptions($input);

        return new BuilderConfig([
            'baselinePath' => $baselineOptions['baselinePath'],
            'config' => $this->loadConfig($configPath),
            'configPath' => $configPath,
            'finder' => $this->loadFinder(
                $rootDirectory,
                $this->getStringOption($input, 'finder', '.php-cs-fixer-finder.php'),
            ),
            'invalidOnly' => (bool) $input->getOption('invalid-only'),
            'relative' => $baselineOptions['relative'],
            'workdir' => $baselineOptions['workdir'],
        ]);
    }

    /**
     * @return array{baselinePath: string, relative: bool, workdir: ?string}
     */
    public function resolveBaselineOptions(InputInterface $input): array
    {
        $rootDirectory = $this->getStringOption($input, 'config-dir', '');

        return [
            'baselinePath' => $this->resolvePath(
                $rootDirectory,
                $this->getStringOption($input, 'baseline', '.php-cs-fixer-baseline.json'),
            ),
            'relative' => !$input->getOption('absolute'),
            'workdir' => $this->getNullableStringOption($input, 'workdir'),
        ];
    }

    private function getNullableStringOption(InputInterface $input, string $name): ?string
    {
        $value = $input->getOption($name);

        return \is_string($value) ? $value : null;
    }

    private function getStringOption(InputInterface $input, string $name, string $default): string
    {
        $value = $input->getOption($name);

        return \is_string($value) ? $value : $default;
    }

    private function loadConfig(string $path): Config
    {
        /** @var Config $config */
        $config = require $path;

        return $config;
    }

    private function loadFinder(string $rootDirectory, string $path): Finder
    {
        /** @var Finder $finder */
        $finder = require $this->resolvePath($rootDirectory, $path);

        return $finder;
    }

    private function resolveFixerPath(string $rootDirectory, InputInterface $input): string
    {
        $files = self::DEFAULT_FIXER_CONFIG_FILES;
        $inputPath = $this->getStringOption($input, 'config', '');
        if ('' !== $inputPath) {
            array_unshift($files, $inputPath);
        }

        foreach ($files as $file) {
            $configPath = $this->resolvePath($rootDirectory, $file);

            if (file_exists($configPath)) {
                return $configPath;
            }
        }

        throw new InvalidArgumentException('Cannot find config file');
    }

    private function resolvePath(string $rootDirectory, string $path): string
    {
        $path = $rootDirectory . $path;

        if (!preg_match('#^(?:[[:alpha:]]:[/\\\\]|/)#', $path)) {
            $path = getcwd() . '/' . $path;
        }

        $realPath = realpath($path);

        return false !== $realPath ? $realPath : $path;
    }
}
