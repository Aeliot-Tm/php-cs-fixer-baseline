<?php

declare(strict_types=1);

namespace Aeliot\PhpCsFixerBaseline\Service;

final class FileCacheCalculator
{
    public function calculate(\SplFileInfo $file): int
    {
        return crc32(file_get_contents($file->getPathname()));
    }
}
