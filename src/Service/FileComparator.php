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
    public function __construct(
        private FileCacheCalculator $fileCacheCalculator = new FileCacheCalculator(),
    ) {
    }

    public function isInBaseLine(BaselineContent $content, \SplFileInfo $file): bool
    {
        $hash = $content->getHash($file->getPathname())?->getHash();

        return $hash && $hash === $this->fileCacheCalculator->calculate($file);
    }
}
