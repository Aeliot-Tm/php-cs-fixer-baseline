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

use Aeliot\PhpCsFixerBaseline\Model\BuilderConfig;
use Aeliot\PhpCsFixerBaseline\Model\FileHash;
use Aeliot\PhpCsFixerBaseline\Service\Builder;
use Aeliot\PhpCsFixerBaseline\Service\ConfigHashCalculator;
use Aeliot\PhpCsFixerBaseline\Service\FileCacheCalculator;
use Aeliot\PhpCsFixerBaseline\Service\InvalidFilesDetector;
use Aeliot\PhpCsFixerBaseline\Service\PathNormalizer;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Aeliot\PhpCsFixerBaseline\Service\Builder
 */
final class BuilderTest extends TestCase
{
    public function testCreate(): void
    {
        $path = '/path/to/baseline';

        $config = $this->createMock(Config::class);
        $config->method('getRiskyAllowed')->willReturn(false);
        $config->method('getRules')->willReturn(['some_rule' => true]);

        $expectedPath = realpath(__DIR__ . '/../../fixtures/file-for-calculation-of-hash.php');

        $files = [];
        $files[] = $file = $this->createMock(\SplFileInfo::class);
        $file->method('getPathname')->willReturn($expectedPath);

        $finder = $this->createMock(Finder::class);
        $finder->method('getIterator')->willReturn(new \ArrayIterator($files));

        $detector = $this->createMock(InvalidFilesDetector::class);
        $detector->expects(self::never())->method('detect');

        $pathNormalizer = new PathNormalizer();

        $builder = new Builder(
            new ConfigHashCalculator(),
            new FileCacheCalculator(),
            $detector,
            $pathNormalizer,
        );
        $builderConfig = new BuilderConfig([
            'baselinePath' => $path,
            'config' => $config,
            'configPath' => '/path/to/config.php',
            'finder' => $finder,
            'relative' => false,
            'workdir' => null,
        ]);
        $baselineFile = $builder->create($builderConfig);

        self::assertSame($path, $baselineFile->getPath());
        self::assertSame(1, $baselineFile->getLockedFilesCount());

        $baselineContent = $baselineFile->getContent();
        self::assertSame(1105664888, $baselineContent->getConfigHash());

        $fileHash = $baselineContent->getHash($expectedPath);
        self::assertInstanceOf(FileHash::class, $fileHash);

        self::assertSame($expectedPath, $fileHash->getPath());
        self::assertSame(4266623405, $fileHash->getHash());
    }

    public function testCreateWithInvalidOnlyIncludesOnlyDetectedFiles(): void
    {
        $path = '/path/to/baseline';

        $config = $this->createMock(Config::class);
        $config->method('getRiskyAllowed')->willReturn(false);
        $config->method('getRules')->willReturn(['some_rule' => true]);

        $includedPath = realpath(__DIR__ . '/../../fixtures/file-for-calculation-of-hash.php');
        $excludedPath = realpath(__DIR__ . '/../../fixtures/file-for-calculation-of-hash-second.php');

        $includedFile = $this->createMock(\SplFileInfo::class);
        $includedFile->method('getPathname')->willReturn((string) $includedPath);

        $excludedFile = $this->createMock(\SplFileInfo::class);
        $excludedFile->method('getPathname')->willReturn((string) $excludedPath);

        $finder = $this->createMock(Finder::class);
        $finder->method('getIterator')->willReturn(new \ArrayIterator([$includedFile, $excludedFile]));

        $pathNormalizer = new PathNormalizer();

        $detector = $this->createMock(InvalidFilesDetector::class);
        $detector
            ->expects(self::once())
            ->method('detect')
            ->willReturn([$pathNormalizer->normalize((string) $includedPath) => true]);

        $builder = new Builder(
            new ConfigHashCalculator(),
            new FileCacheCalculator(),
            $detector,
            $pathNormalizer,
        );
        $builderConfig = new BuilderConfig([
            'baselinePath' => $path,
            'config' => $config,
            'configPath' => '/path/to/config.php',
            'finder' => $finder,
            'invalidOnly' => true,
            'relative' => false,
            'workdir' => null,
        ]);
        $baselineFile = $builder->create($builderConfig);

        self::assertSame(1, $baselineFile->getLockedFilesCount());
        self::assertNotNull($baselineFile->getContent()->getHash((string) $includedPath));
        self::assertNull($baselineFile->getContent()->getHash((string) $excludedPath));
    }
}
