<?php

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
