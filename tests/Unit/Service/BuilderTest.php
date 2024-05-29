<?php

declare(strict_types=1);

namespace Aeliot\PhpCsFixerBaseline\Test\Unit\Service;

use Aeliot\PhpCsFixerBaseline\Model\FileHash;
use Aeliot\PhpCsFixerBaseline\Service\Builder;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Builder::class)]
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

        $builder = new Builder();
        $baselineFile = $builder->create($path, $config, $finder);

        self::assertSame($path, $baselineFile->getPath());
        self::assertSame(1, $baselineFile->getLockedFilesCount());

        $baselineContent = $baselineFile->getContent();
        self::assertSame(1105664888, $baselineContent->getConfigHash());

        $fileHash = $baselineContent->getHash($expectedPath);
        self::assertInstanceOf(FileHash::class, $fileHash);

        self::assertSame($expectedPath, $fileHash->getPath());
        self::assertSame(4266623405, $fileHash->getHash());
    }
}
