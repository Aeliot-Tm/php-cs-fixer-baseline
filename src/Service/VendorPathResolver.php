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

final class VendorPathResolver
{
    public function resolveAutoloaderPath(string $binaryDirectory): string
    {
        if ('' !== \Phar::running()) {
            return $binaryDirectory . '/../vendor/autoload.php';
        }

        if (isset($GLOBALS['_composer_autoload_path']) && \is_string($GLOBALS['_composer_autoload_path'])) {
            return $GLOBALS['_composer_autoload_path'];
        }

        foreach ($this->getFallbackAutoloaderPaths($binaryDirectory) as $path) {
            if (!is_file($path)) {
                continue;
            }

            $realPath = realpath($path);

            return false !== $realPath ? $realPath : $path;
        }

        throw new \RuntimeException('Cannot find autoloader');
    }

    /**
     * @return list<string>
     */
    public function getVendorRoots(string $packageRoot): array
    {
        $roots = [];

        if (isset($GLOBALS['_composer_autoload_path']) && \is_string($GLOBALS['_composer_autoload_path'])) {
            $roots[] = \dirname($GLOBALS['_composer_autoload_path'], 2);
        }

        $roots[] = $packageRoot;

        if ('' !== \Phar::running()) {
            $roots[] = \dirname(\Phar::running(false));
        }

        return array_values(array_unique($roots));
    }

    /**
     * @return list<string>
     */
    private function getFallbackAutoloaderPaths(string $binaryDirectory): array
    {
        return [
            $binaryDirectory . '/../../../autoload.php',
            $binaryDirectory . '/../vendor/autoload.php',
            $binaryDirectory . '/../../../../vendor/autoload.php',
            $binaryDirectory . '/../../vendor/autoload.php',
        ];
    }
}
