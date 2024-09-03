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

use PhpCsFixer\Config;

final class FilterFactory
{
    public function createFilter(string $path, Config $config, ?string $workdir = null): \Closure
    {
        $baseline = (new Reader())->read($path)->getContent();
        $isSameConfig = $baseline->getConfigHash() === (new ConfigHashCalculator())->calculate($config);

        if ($baseline->isRelative()) {
            $baseline->setWorkdir($workdir ?? getcwd());
        }

        $comparator = new FileComparator();

        return static function (\SplFileInfo $file) use ($isSameConfig, $baseline, $comparator): bool {
            return !$isSameConfig || !$comparator->isInBaseLine($baseline, $file);
        };
    }
}
