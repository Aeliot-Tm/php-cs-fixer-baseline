<?php

namespace Aeliot\PhpCsFixerBaseline\Service;

final class FileCacheCalculator
{
    public function calculate(\SplFileInfo $file): string
    {
        return crc32(file_get_contents($file->getPathname()));
    }
}