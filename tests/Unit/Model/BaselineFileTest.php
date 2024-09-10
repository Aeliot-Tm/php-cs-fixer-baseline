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

namespace Aeliot\PhpCsFixerBaseline\Test\Unit\Model;

use Aeliot\PhpCsFixerBaseline\Model\BaselineContent;
use Aeliot\PhpCsFixerBaseline\Model\BaselineFile;
use Aeliot\PhpCsFixerBaseline\Model\FileHash;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BaselineFile::class)]
final class BaselineFileTest extends TestCase
{
    public function testSamePathAndContent(): void
    {
        $path = '/path/to/file';
        $baselineContent = new BaselineContent();
        $baselineFile = new BaselineFile($path, $baselineContent);

        self::assertSame($path, $baselineFile->getPath());
        self::assertSame($baselineContent, $baselineFile->getContent());
    }

    public function testLockedFilesCount(): void
    {
        $baselineContent = new BaselineContent();
        $baselineFile = new BaselineFile('any string', $baselineContent);

        self::assertSame(0, $baselineFile->getLockedFilesCount());

        $baselineContent->addHash(new FileHash('/path/to/first-file', /* any hash */ 0));
        self::assertSame(1, $baselineFile->getLockedFilesCount());
        self::assertSame($baselineContent->getHashesCount(), $baselineFile->getLockedFilesCount());

        $baselineContent->addHash(new FileHash('/path/to/second-file', /* any hash */ 0));
        self::assertSame(2, $baselineFile->getLockedFilesCount());
        self::assertSame($baselineContent->getHashesCount(), $baselineFile->getLockedFilesCount());
    }
}
