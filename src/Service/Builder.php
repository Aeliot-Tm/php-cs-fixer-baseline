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

use Aeliot\PhpCsFixerBaseline\Model\BaselineContent;
use Aeliot\PhpCsFixerBaseline\Model\BaselineFile;
use Aeliot\PhpCsFixerBaseline\Model\BuilderConfig;
use Aeliot\PhpCsFixerBaseline\Model\FileHash;

final class Builder
{
    public function __construct(
        private readonly ConfigHashCalculator $configHashCalculator,
        private readonly FileCacheCalculator $fileCacheCalculator,
        private readonly InvalidFilesDetector $invalidFilesDetector,
        private readonly PathNormalizer $pathNormalizer,
    ) {
    }

    public function create(BuilderConfig $config): BaselineFile
    {
        $content = new BaselineContent();
        $content->setConfigHash($this->configHashCalculator->calculate($config->getConfig()));

        $isRelative = $config->isRelative();
        $allowedPaths = $config->isInvalidOnly()
            ? $this->invalidFilesDetector->detect($config)
            : null;

        foreach ($config->getFinder() as $file) {
            if (null !== $allowedPaths) {
                $normalizedPath = $this->pathNormalizer->normalize(
                    $file->getPathname(),
                    $config->getWorkdir() ?? getcwd() ?: null,
                );

                if (!isset($allowedPaths[$normalizedPath])) {
                    continue;
                }
            }

            $filePath = $this->getFilePath($file, $isRelative);
            $content->addHash(new FileHash($filePath, $this->fileCacheCalculator->calculate($file)));
        }

        if ($isRelative) {
            $content->setWorkdir($config->getWorkdir() ?? getcwd());
        }

        return new BaselineFile($config->getBaselinePath(), $content);
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
