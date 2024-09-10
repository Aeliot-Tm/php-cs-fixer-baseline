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

namespace Aeliot\PhpCsFixerBaseline\Model;

final class BaselineFile
{
    public function __construct(
        private string $path,
        private BaselineContent $content,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getContent(): BaselineContent
    {
        return $this->content;
    }

    public function getLockedFilesCount(): int
    {
        return $this->content->getHashesCount();
    }
}
