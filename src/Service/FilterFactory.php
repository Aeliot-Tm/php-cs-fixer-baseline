<?php

namespace Aeliot\PhpCsFixerBaseline\Service;

use PhpCsFixer\Config;

final class FilterFactory
{
    public function createFilter(string $path, Config $config): \Closure
    {
        $baseline = (new Reader())->read($path)->getContent();
        $isSameConfig = $baseline->getConfigHash() === (new ConfigHashCalculator())->calculate($config);
        $comparator = new FileComparator();

        return static function (\SplFileInfo $file) use ($isSameConfig, $baseline, $comparator): bool {
            return !$isSameConfig || $comparator->isInBaseLine($baseline, $file);
        };
    }
}
