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

namespace Aeliot\PhpCsFixerBaseline\Test\Unit\Service;

use Aeliot\PhpCsFixerBaseline\Service\PathNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Aeliot\PhpCsFixerBaseline\Service\PathNormalizer
 */
final class PathNormalizerTest extends TestCase
{
    private PathNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new PathNormalizer();
    }

    public function testNormalizeResolvesRelativePathAgainstWorkdir(): void
    {
        $workdir = '/builds/group/project';

        self::assertSame(
            '/builds/group/project/tests/fixtures/invalid-only/non-compliant-first.php',
            $this->normalizer->normalize('tests/fixtures/invalid-only/non-compliant-first.php', $workdir),
        );
    }

    public function testNormalizeProducesSameKeyForAbsoluteAndRelativePaths(): void
    {
        $workdir = '/builds/group/project';
        $absolute = '/builds/group/project/tests/fixtures/invalid-only/non-compliant-first.php';
        $relative = 'tests/fixtures/invalid-only/non-compliant-first.php';

        self::assertSame(
            $this->normalizer->normalize($absolute),
            $this->normalizer->normalize($relative, $workdir),
        );
    }

    public function testNormalizeCollapsesDotSegments(): void
    {
        $workdir = '/builds/group/project';

        self::assertSame(
            '/builds/group/project/tests/fixtures/file.php',
            $this->normalizer->normalize('/builds/group/project/./tests/../tests/fixtures/file.php'),
        );

        self::assertSame(
            '/builds/group/project/tests/fixtures/file.php',
            $this->normalizer->normalize('tests/../tests/fixtures/file.php', $workdir),
        );
    }

    public function testNormalizeStripsWorkdirBasenamePrefixFromRelativePath(): void
    {
        $workdir = '/home/runner/work/php-cs-fixer-baseline/php-cs-fixer-baseline';

        self::assertSame(
            '/home/runner/work/php-cs-fixer-baseline/php-cs-fixer-baseline/tests/fixtures/invalid-only/non-compliant-first.php',
            $this->normalizer->normalize(
                'php-cs-fixer-baseline/tests/fixtures/invalid-only/non-compliant-first.php',
                $workdir,
            ),
        );
    }

    public function testNormalizeSplFileInfoUsesPathnameWithoutSymlinkResolution(): void
    {
        $workdir = '/builds/group/project';
        $file = $this->createMock(\SplFileInfo::class);
        $file->method('getPathname')->willReturn('tests/fixtures/invalid-only/non-compliant-first.php');
        $file->method('getRealPath')->willReturn('/tmp/canonical/non-compliant-first.php');

        self::assertSame(
            '/builds/group/project/tests/fixtures/invalid-only/non-compliant-first.php',
            $this->normalizer->normalizeSplFileInfo($file, $workdir),
        );
    }
}
