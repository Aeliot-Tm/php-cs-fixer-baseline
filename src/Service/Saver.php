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

use Aeliot\PhpCsFixerBaseline\Model\BaselineFile;

final class Saver
{
    public function save(BaselineFile $baseline): void
    {
        $content = json_encode($baseline->getContent(), \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
        file_put_contents($baseline->getPath(), $content);
    }
}
