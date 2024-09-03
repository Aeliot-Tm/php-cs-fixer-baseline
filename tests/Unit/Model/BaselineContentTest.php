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

namespace Aeliot\PhpCsFixerBaseline\Test\Unit\Model;

use Aeliot\PhpCsFixerBaseline\Model\BaselineContent;
use Aeliot\PhpCsFixerBaseline\Model\FileHash;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BaselineContent::class)]
final class BaselineContentTest extends TestCase
{
    public function testGetConfigHash(): void
    {
        $baselineContent = new BaselineContent();

        self::assertNull($baselineContent->getConfigHash());

        $configHash = 1;
        $baselineContent->setConfigHash($configHash);
        self::assertSame($configHash, $baselineContent->getConfigHash());
    }

    public function testGetHash(): void
    {
        $baselineContent = new BaselineContent();

        $pathA = '/path/to/file-a';
        $hashA = new FileHash($pathA, /* any hash */ 0);
        $baselineContent->addHash($hashA);

        $pathB = '/path/to/file-b';
        $hashB = new FileHash($pathB, /* any hash */ 0);
        $baselineContent->addHash($hashB);

        self::assertSame($hashA, $baselineContent->getHash($pathA));
        self::assertSame($hashB, $baselineContent->getHash($pathB));
    }

    public function testHashesCount(): void
    {
        $baselineContent = new BaselineContent();

        self::assertSame(0, $baselineContent->getHashesCount());

        $baselineContent->addHash(new FileHash('/path/to/first-file', /* any hash */ 0));
        self::assertSame(1, $baselineContent->getHashesCount());

        // add duplicated hash
        $baselineContent->addHash(new FileHash('/path/to/first-file', /* any hash */ 0));
        self::assertSame(1, $baselineContent->getHashesCount());

        $baselineContent->addHash(new FileHash('/path/to/second-file', /* any hash */ 0));
        self::assertSame(2, $baselineContent->getHashesCount());
    }

    public function testJsonSerialiseConfigHash(): void
    {
        $baselineContent = new BaselineContent();
        self::assertSame(['relative' => false, 'hashes' => []], $baselineContent->jsonSerialize());

        $baselineContent->setConfigHash(0);
        self::assertSame(['config_hash' => 0, 'relative' => false, 'hashes' => []], $baselineContent->jsonSerialize());
    }

    public function testJsonSerialiseHashes(): void
    {
        $baselineContent = new BaselineContent();
        self::assertSame(['relative' => false, 'hashes' => []], $baselineContent->jsonSerialize());

        $baselineContent->addHash(new FileHash('/path/to/file-b', 1));
        $expectedData = [
            'relative' => false,
            'hashes' => [
                '/path/to/file-b' => ['hash' => 1],
            ],
        ];
        self::assertSame($expectedData, $baselineContent->jsonSerialize());

        $baselineContent->addHash(new FileHash('/path/to/file-c', 2));
        $expectedData = [
            'relative' => false,
            'hashes' => [
                '/path/to/file-b' => ['hash' => 1],
                '/path/to/file-c' => ['hash' => 2],
            ],
        ];
        self::assertSame($expectedData, $baselineContent->jsonSerialize());

        $baselineContent->addHash(new FileHash('/path/to/file-a', 3));
        $expectedData = [
            'relative' => false,
            'hashes' => [
                '/path/to/file-a' => ['hash' => 3],
                '/path/to/file-b' => ['hash' => 1],
                '/path/to/file-c' => ['hash' => 2],
            ],
        ];
        self::assertSame($expectedData, $baselineContent->jsonSerialize());
    }
}
