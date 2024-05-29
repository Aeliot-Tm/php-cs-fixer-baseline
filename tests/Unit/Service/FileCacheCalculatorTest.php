<?php

declare(strict_types=1);

namespace Aeliot\PhpCsFixerBaseline\Test\Unit\Service;

use Aeliot\PhpCsFixerBaseline\Service\FileCacheCalculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileCacheCalculator::class)]
final class FileCacheCalculatorTest extends TestCase
{
    public function testCalculate(): void
    {
        $file = $this->createMock(\SplFileInfo::class);
        $file
            ->method('getPathname')
            ->willReturn(__DIR__.'/../../fixtures/file-for-calculation-of-hash.php');

        $calculator = new FileCacheCalculator();
        self::assertSame(3067467297, $calculator->calculate($file));
    }
}