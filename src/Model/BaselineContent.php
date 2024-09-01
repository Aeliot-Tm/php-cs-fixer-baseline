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

namespace Aeliot\PhpCsFixerBaseline\Model;

final class BaselineContent implements \JsonSerializable
{
    private ?int $configHash = null;
    /**
     * @var array<string,FileHash>
     */
    private array $hashes = [];
    private bool $relative = false;
    private ?string $workdir = null;

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

    public function setRelative(bool $relative): void
    {
        $this->relative = $relative;
    }

    public function getWorkdir(): ?string
    {
        return $this->workdir;
    }

    public function setWorkdir(?string $workdir): void
    {
        $this->relative = (bool) $workdir;
        $this->workdir = $workdir;
    }

    /**
     * @return array<string,array<string,string>|int>
     */
    public function jsonSerialize(): array
    {
        $baseline = [];
        if (null !== $this->configHash) {
            $baseline['config_hash'] = $this->configHash;
        }

        $hashes = array_map(static fn (FileHash $x): array => $x->jsonSerialize(), $this->hashes);

        $baseline['relative'] = $this->relative;
        if ($this->workdir) {
            $workdirLength = mb_strlen($this->workdir);
            if (\DIRECTORY_SEPARATOR !== $this->workdir[$workdirLength - 1]) {
                ++$workdirLength;
            }
            $hashes = array_combine(
                array_map(
                    function (string $path) use ($workdirLength): string {
                        if (str_starts_with($path, $this->workdir)
                            && mb_strlen($path) > $workdirLength
                            && \in_array($path[$workdirLength - 1], ['/', '\\'], true)
                        ) {
                            $path = substr($path, $workdirLength);
                        }

                        return $path;
                    },
                    array_keys($hashes),
                ),
                $hashes,
            );
        }

        ksort($hashes);
        $baseline['hashes'] = $hashes;

        return $baseline;
    }
}
