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

use Aeliot\PhpCsFixerBaseline\Dto\FilterOptions;

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

    /**
     * @param \PhpCsFixer\Config|\PhpCsFixer\ConfigInterface $fixerConfig
     */
    public function createFilter(string $path, $fixerConfig, ?FilterOptions $options = null): \Closure
    {
        if (!(is_a($fixerConfig, 'PhpCsFixer\Config') || is_a($fixerConfig, 'PhpCsFixer\ConfigInterface'))) {
            throw new \InvalidArgumentException('Fixer config must be an instance of PhpCsFixer\Config or PhpCsFixer\ConfigInterface');
        }

        $baseline = $this->reader->read($path)->getContent();
        $isSameConfig = $baseline->getConfigHash() === $this->configHashCalculator->calculate($fixerConfig);

        if ($baseline->isRelative()) {
            $baseline->setWorkdir($options?->getWorkdir() ?? getcwd());
        }

        $comparator = $this->fileComparator;

        return static function (\SplFileInfo $file) use ($isSameConfig, $baseline, $comparator): bool {
            return !$isSameConfig || !$comparator->isInBaseLine($baseline, $file);
        };
    }
}
