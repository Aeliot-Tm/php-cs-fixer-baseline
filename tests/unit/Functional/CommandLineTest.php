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

namespace Aeliot\PhpCsFixerBaseline\Test\Unit\Functional;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class CommandLineTest extends TestCase
{
    public function testCommandLine(): void
    {
        $baselinePath = __DIR__ . '/../../config/.php-cs-fixer-baseline.json';

        if (file_exists($baselinePath)) {
            unlink($baselinePath);
        }

        shell_exec('php bin/pcsf-baseline -d tests/config/');

        self::assertFileExists($baselinePath);
    }
}
