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

use PhpCsFixer\Config;

final class FilterFactory
{
    private readonly Reader $reader;
    private readonly ConfigHashCalculator $configHashCalculator;
    private readonly FileComparator $fileComparator;

    public function __construct(
        ?Reader $reader = null,
        ?ConfigHashCalculator $configHashCalculator = null,
        ?FileComparator $fileComparator = null,
    ) {
        $this->reader = $reader ?? new Reader();
        $this->configHashCalculator = $configHashCalculator ?? new ConfigHashCalculator();
        $this->fileComparator = $fileComparator ?? new FileComparator();
    }

    public function createFilter(string $path, Config $config, ?string $workdir = null): \Closure
    {
        $baseline = $this->reader->read($path)->getContent();
        $isSameConfig = $baseline->getConfigHash() === $this->configHashCalculator->calculate($config);

        if ($baseline->isRelative()) {
            $baseline->setWorkdir($workdir ?? getcwd());
        }

        $comparator = $this->fileComparator;

        return static function (\SplFileInfo $file) use ($isSameConfig, $baseline, $comparator): bool {
            return !$isSameConfig || !$comparator->isInBaseLine($baseline, $file);
        };
    }
}
