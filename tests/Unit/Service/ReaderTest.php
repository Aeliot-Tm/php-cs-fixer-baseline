<?php

declare(strict_types=1);

namespace Aeliot\PhpCsFixerBaseline\Test\Unit\Service;

use Aeliot\PhpCsFixerBaseline\Model\FileHash;
use Aeliot\PhpCsFixerBaseline\Service\Reader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Reader::class)]
final class ReaderTest extends TestCase
{
    public function testNotExistingPath(): void
    {
        $path = '/path/to/not-existing-file';
        $baselineFile = (new Reader())->read($path);

        self::assertSame($path, $baselineFile->getPath());
        self::assertSame(0, $baselineFile->getLockedFilesCount());

        $baselineContent = $baselineFile->getContent();
        self::assertNull($baselineContent->getConfigHash());
    }

    public function testExistingPath(): void
    {
        $this->markTestIncomplete('Test fallen on CI. Soon of it is depends on EOL config of git');

        $path = __DIR__ . '/../../fixtures/.php-cs-fixer-baseline.json';
        $expectedPath = realpath(__DIR__ . '/../../fixtures/file-for-calculation-of-hash.php');
        $baselineFile = (new Reader())->read($path);

        self::assertSame($path, $baselineFile->getPath());
        self::assertSame(1, $baselineFile->getLockedFilesCount());

        $baselineContent = $baselineFile->getContent();
        self::assertSame(1624530864, $baselineContent->getConfigHash());

        $fileHash = $baselineContent->getHash($expectedPath);
        self::assertInstanceOf(FileHash::class, $fileHash);

        self::assertSame($expectedPath, $fileHash->getPath());
        self::assertSame(4266623405, $fileHash->getHash());
    }
}
