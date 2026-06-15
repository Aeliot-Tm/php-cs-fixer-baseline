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

final class PhpCsFixerBinaryResolver
{
    public function resolve(): string
    {
        $env = getenv('PHP_CS_FIXER_BINARY');
        if (\is_string($env) && '' !== $env) {
            return $env;
        }

        foreach ($this->getProjectRoots() as $root) {
            $candidate = $root . '/vendor/bin/php-cs-fixer';
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        $pathBinary = $this->findInPath('php-cs-fixer');
        if (null !== $pathBinary) {
            return $pathBinary;
        }

        throw new \RuntimeException('Cannot find php-cs-fixer binary. Install friendsofphp/php-cs-fixer or set PHP_CS_FIXER_BINARY.');
    }

    /**
     * @return list<string>
     */
    private function getProjectRoots(): array
    {
        $roots = [\dirname(__DIR__, 2)];

        if ('' !== \Phar::running()) {
            $roots[] = \dirname(\Phar::running(false));
        }

        if (isset($GLOBALS['_composer_autoload_path']) && \is_string($GLOBALS['_composer_autoload_path'])) {
            $roots[] = \dirname($GLOBALS['_composer_autoload_path'], 2);
        }

        return array_values(array_unique($roots));
    }

    private function findInPath(string $binary): ?string
    {
        $path = getenv('PATH');
        if (!\is_string($path) || '' === $path) {
            return null;
        }

        foreach (explode(\PATH_SEPARATOR, $path) as $directory) {
            $candidate = rtrim($directory, '/\\') . '/' . $binary;
            if (is_file($candidate)) {
                return $candidate;
            }

            if (is_file($candidate . '.bat')) {
                return $candidate . '.bat';
            }
        }

        return null;
    }
}
