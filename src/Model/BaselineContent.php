<?php

declare(strict_types=1);

namespace Aeliot\PhpCsFixerBaseline\Model;

final class BaselineContent implements \JsonSerializable
{
    private ?int $configHash = null;
    /**
     * @var array<string,FileHash>
     */
    private array $hashes = [];

    public function getConfigHash(): ?int
    {
        return $this->configHash;
    }

    public function setConfigHash(int $configHash): void
    {
        $this->configHash = $configHash;
    }

    public function addHash(FileHash $hash): void
    {
        $this->hashes[$hash->getPath()] = $hash;
    }

    public function getHash(string $path): ?FileHash
    {
        return $this->hashes[$path] ?? null;
    }

    public function getHashesCount(): int
    {
        return \count($this->hashes);
    }

    /**
     * @return array<string,string|array<string,string>>
     */
    public function jsonSerialize(): array
    {
        $baseline = [];
        if ($this->configHash) {
            $baseline['config_hash'] = $this->configHash;
        }

        $hashes = array_map(static fn(FileHash $x): array => $x->jsonSerialize(), $this->hashes);
        ksort($hashes);
        $baseline['hashes'] = $hashes;

        return $baseline;
    }
}
