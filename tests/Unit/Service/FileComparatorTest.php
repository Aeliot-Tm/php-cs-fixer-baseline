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

use Aeliot\PhpCsFixerBaseline\Model\BaselineContent;
use Aeliot\PhpCsFixerBaseline\Model\FileHash;
use Aeliot\PhpCsFixerBaseline\Service\FileComparator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileComparator::class)]
final class FileComparatorTest extends TestCase
{
    public function testInBaseLine(): void
    {
        $file = $this->mockSplFileInfo();
        $baselineContent = new BaselineContent();
        $baselineContent->addHash(new FileHash($file->getPathname(), 4266623405));

        $comparator = new FileComparator();

        self::assertTrue($comparator->isInBaseLine($baselineContent, $file));
    }

    public function testNotInBaseLine(): void
    {
        $file = $this->mockSplFileInfo();
        $baselineContent = new BaselineContent();
        $comparator = new FileComparator();

        self::assertFalse($comparator->isInBaseLine($baselineContent, $file));
    }

    private function mockSplFileInfo(): \SplFileInfo
    {
        $file = $this->createMock(\SplFileInfo::class);
        $file
            ->method('getPathname')
            ->willReturn(__DIR__ . '/../../fixtures/file-for-calculation-of-hash.php');

        return $file;
    }
}
