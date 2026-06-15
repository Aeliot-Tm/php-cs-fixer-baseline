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
    public function normalize(string $path, ?string $workdir = null): string
    {
        if (null !== $workdir && !preg_match('#^(?:[[:alpha:]]:[/\\\\]|/)#', $path)) {
            $path = rtrim($workdir, '/\\') . '/' . ltrim($path, '/\\');
        }

        $realPath = realpath($path);

        return false !== $realPath ? $realPath : $path;
    }
}
