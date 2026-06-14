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

final class FileComparator
{
    public const MODE_BY_HASH = 'by_hash';
    public const MODE_MENTIONED = 'mentioned';
    public const MODES = [
        self::MODE_BY_HASH,
        self::MODE_MENTIONED,
    ];

    public function __construct(
        private FileCacheCalculator $fileCacheCalculator = new FileCacheCalculator(),
    ) {
    }

    public function isInBaseLine(BaselineContent $content, \SplFileInfo $file, string $mode): bool
    {
        $hash = $content->getHash($file->getPathname())?->getHash();

        return match ($mode) {
            self::MODE_BY_HASH => $hash && $hash === $this->fileCacheCalculator->calculate($file),
            self::MODE_MENTIONED => null !== $hash,
            default => false,
        };
    }
}
