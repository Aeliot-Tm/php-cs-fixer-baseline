<?php

namespace Aeliot\PhpCsFixerBaseline\Model;

final class FileHash implements \JsonSerializable
{
    public function __construct(
        private string $path,
        private string $hash,
    ) {
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array<string,string>
     */
    public function jsonSerialize(): array
    {
        return ['hash' => $this->hash];
    }
}
