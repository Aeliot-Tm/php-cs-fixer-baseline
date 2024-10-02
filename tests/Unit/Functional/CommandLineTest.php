<?php

declare(strict_types=1);

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
