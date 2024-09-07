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

final class FileHash implements \JsonSerializable
{
    public function __construct(
        private string $path,
        private int $hash,
    ) {
    }

    public function getHash(): int
    {
        return $this->hash;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array<string,int>
     */
    public function jsonSerialize(): array
    {
        return ['hash' => $this->hash];
    }
}
