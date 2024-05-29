<?php

declare(strict_types=1);

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
