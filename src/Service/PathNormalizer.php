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

final class PathNormalizer
{
    public function normalizeSplFileInfo(\SplFileInfo $file, ?string $workdir = null): string
    {
        $path = $file->getPathname();
        if ('' === $path) {
            $realPath = $file->getRealPath();
            $path = \is_string($realPath) ? $realPath : '';
        }

        return $this->normalize($path, $workdir);
    }

    public function normalize(string $path, ?string $workdir = null): string
    {
        $path = str_replace('\\', '/', $path);

        if (null !== $workdir && '' !== $workdir && !$this->isAbsolute($path)) {
            $workdir = $this->canonicalize($workdir);
            $path = $this->resolveRelativePath($path, $workdir);
            $path = rtrim($workdir, '/') . '/' . ltrim($path, '/');
        }

        return $this->canonicalize($path);
    }

    private function resolveRelativePath(string $path, string $workdir): string
    {
        $path = ltrim($path, '/');
        $workdirBasename = basename($workdir);
        $prefix = $workdirBasename . '/';

        if ('' !== $workdirBasename && str_starts_with($path, $prefix)) {
            return substr($path, \strlen($prefix));
        }

        return $path;
    }

    private function isAbsolute(string $path): bool
    {
        return (bool) preg_match('#^(?:[[:alpha:]]:[/\\\\]|/)#', $path);
    }

    private function canonicalize(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        if ('' === $path) {
            return '';
        }

        $prefix = '';
        if (preg_match('#^([[:alpha:]]:)(.*)$#', $path, $matches)) {
            $prefix = $matches[1];
            $path = $matches[2];
        }

        $isAbsolute = str_starts_with($path, '/');
        $segments = explode('/', $path);
        $resolved = [];

        foreach ($segments as $segment) {
            if ('' === $segment || '.' === $segment) {
                continue;
            }

            if ('..' === $segment) {
                if ([] !== $resolved) {
                    array_pop($resolved);
                }

                continue;
            }

            $resolved[] = $segment;
        }

        if ($isAbsolute) {
            return $prefix . '/' . implode('/', $resolved);
        }

        return $prefix . implode('/', $resolved);
    }
}
