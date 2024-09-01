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

use Aeliot\PhpCsFixerBaseline\Model\BaselineContent;
use Aeliot\PhpCsFixerBaseline\Model\BaselineFile;
use Aeliot\PhpCsFixerBaseline\Model\BuilderConfig;
use Aeliot\PhpCsFixerBaseline\Model\FileHash;

final class Builder
{
    private ConfigHashCalculator $configHashCalculator;
    private FileCacheCalculator $fileCacheCalculator;

    public function __construct()
    {
        $this->configHashCalculator = new ConfigHashCalculator();
        $this->fileCacheCalculator = new FileCacheCalculator();
    }

    public function create(BuilderConfig $config): BaselineFile
    {
        $content = new BaselineContent();
        $content->setConfigHash($this->configHashCalculator->calculate($config->getConfig()));

        $isRelative = $config->isRelative();

        $rootDir = null;
        foreach ($config->getFinder() as $file) {
            $filePath = $this->getFilePath($file, $isRelative);
            if ($isRelative) {
                $rootDir = $this->extractPathPrefix($rootDir, $filePath);
            }
            $content->addHash(new FileHash($filePath, $this->fileCacheCalculator->calculate($file)));
        }

        $content->setWorkdir($config->getWorkdir() ?? $rootDir);

        return new BaselineFile($config->getBaselinePath(), $content);
    }

    private function extractPathPrefix(?string $rootDir, string $filePath): string
    {
        $currentDir = \dirname($filePath);
        if (null === $rootDir) {
            return $currentDir;
        }

        $rootParts = explode(\DIRECTORY_SEPARATOR, $rootDir);
        $currentParts = explode(\DIRECTORY_SEPARATOR, $currentDir);

        $minSize = min(\count($rootParts), \count($currentParts));

        $rootSliced = \array_slice($rootParts, 0, $minSize);
        $currentSliced = \array_slice($currentParts, 0, $minSize);

        for ($i = 0; $i < $minSize; ++$i) {
            if ($rootSliced[$i] !== $currentSliced[$i]) {
                $rootSliced = \array_slice($rootSliced, 0, $i);
                break;
            }
        }

        return implode(\DIRECTORY_SEPARATOR, $rootSliced);
    }

    public function getFilePath(\SplFileInfo $file, bool $isRelative): string
    {
        $filePath = $file->getPathname();
        if ($isRelative) {
            $realPath = realpath($filePath);
            if ($realPath) {
                $filePath = $realPath;
            }
        }

        return $filePath;
    }
}
