<?php

namespace Aeliot\PhpCsFixerBaseline\Test\Unit\Model;

use Aeliot\PhpCsFixerBaseline\Model\FileHash;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileHash::class)]
final class FileHashTest extends TestCase
{
    public function testGetters(): void
    {
        $hash = 'h';
        $path = 'p';
        $fileHash = new FileHash($path, $hash);

        self::assertSame($hash, $fileHash->getHash());
        self::assertSame($path, $fileHash->getPath());
        self::assertSame(['hash' => $hash], $fileHash->jsonSerialize());
    }
}
