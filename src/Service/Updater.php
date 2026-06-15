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
use Aeliot\PhpCsFixerBaseline\Model\BaselineFile;
use Aeliot\PhpCsFixerBaseline\Model\FileHash;

final class Updater
{
    public function __construct(
        private readonly Reader $reader,
        private readonly FileCacheCalculator $fileCacheCalculator,
    ) {
    }

    /**
     * @param array{baselinePath: string, relative: bool, workdir: ?string} $context
     * @param list<string> $filePaths
     */
    public function update(array $context, array $filePaths): BaselineFile
    {
        $baselinePath = $context['baselinePath'];

        if (!file_exists($baselinePath)) {
            throw new InvalidArgumentException(\sprintf('Baseline file "%s" does not exist.', $baselinePath));
        }

        $baselineFile = $this->reader->read($baselinePath);
        $content = $baselineFile->getContent();

        if ($content->isRelative()) {
            $content->setWorkdir($context['workdir'] ?? getcwd());
        }

        foreach ($filePaths as $filePath) {
            $absolutePath = realpath($filePath);

            if (false === $absolutePath || !is_file($absolutePath)) {
                throw new InvalidArgumentException(\sprintf('File "%s" does not exist.', $filePath));
            }

            $existing = $content->getHash($absolutePath);

            if (null === $existing) {
                throw new InvalidArgumentException(\sprintf('File "%s" is not in baseline.', $filePath));
            }

            $hash = $this->fileCacheCalculator->calculate(new \SplFileInfo($absolutePath));
            $content->addHash(new FileHash($existing->getPath(), $hash));
        }

        return $baselineFile;
    }
}
