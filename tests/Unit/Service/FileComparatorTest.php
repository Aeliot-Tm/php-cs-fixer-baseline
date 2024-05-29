<?php

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
        $baselineContent->addHash(new FileHash($file->getPathname(), 3067467297));

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
            ->willReturn(__DIR__.'/../../fixtures/file-for-calculation-of-hash.php');

        return $file;
    }
}