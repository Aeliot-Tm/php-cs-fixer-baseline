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
            ->willReturn(__DIR__ . '/../../fixtures/file-for-calculation-of-hash.php');

        $calculator = new FileCacheCalculator();
        self::assertSame(4266623405, $calculator->calculate($file));
    }
}
