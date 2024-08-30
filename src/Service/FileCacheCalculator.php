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

final class FileCacheCalculator
{
    public function calculate(\SplFileInfo $file): int
    {
        return crc32(file_get_contents($file->getPathname()));
    }
}
