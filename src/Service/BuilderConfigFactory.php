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

use Aeliot\PhpCsFixerBaseline\Model\BuilderConfig;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use Symfony\Component\Console\Input\InputInterface;

final class BuilderConfigFactory
{
    public function createFromInput(InputInterface $input): BuilderConfig
    {
        $rootDirectory = $this->getStringOption($input, 'config-dir', '');

        return new BuilderConfig([
            'baselinePath' => $this->resolvePath(
                $rootDirectory,
                $this->getStringOption($input, 'baseline', '.php-cs-fixer-baseline.json'),
            ),
            'config' => $this->loadConfig(
                $rootDirectory,
                $this->getStringOption($input, 'config', '.php-cs-fixer.dist.php'),
            ),
            'finder' => $this->loadFinder(
                $rootDirectory,
                $this->getStringOption($input, 'finder', '.php-cs-fixer-finder.php'),
            ),
            'relative' => !$input->getOption('absolute'),
            'workdir' => $this->getNullableStringOption($input, 'workdir'),
        ]);
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

    private function loadConfig(string $rootDirectory, string $path): Config
    {
        /** @var Config $config */
        $config = require $this->resolvePath($rootDirectory, $path);

        return $config;
    }

    private function loadFinder(string $rootDirectory, string $path): Finder
    {
        /** @var Finder $finder */
        $finder = require $this->resolvePath($rootDirectory, $path);

        return $finder;
    }

    private function resolvePath(string $rootDirectory, string $path): string
    {
        $path = $rootDirectory . $path;

        if (preg_match('#^(?:[[:alpha:]]:[/\\\\]|/)#', $path)) {
            return $path;
        }

        return getcwd() . '/' . $path;
    }
}
