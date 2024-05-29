<?php

declare(strict_types=1);

namespace Aeliot\PhpCsFixerBaseline\Test\Unit\Model;

use Aeliot\PhpCsFixerBaseline\Model\FileHash;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileHash::class)]
final class FileHashTest extends TestCase
{
    public function testGetters(): void
    {
        $hash = 1;
        $path = 'p';
        $fileHash = new FileHash($path, $hash);

        self::assertSame($hash, $fileHash->getHash());
        self::assertSame($path, $fileHash->getPath());
        self::assertSame(['hash' => $hash], $fileHash->jsonSerialize());
    }
}
