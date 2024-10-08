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

use Aeliot\PhpCsFixerBaseline\Service\ConfigHashCalculator;
use PhpCsFixer\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigHashCalculator::class)]
final class ConfigHashCalculatorTest extends TestCase
{
    /**
     * @param array<string, array<string, mixed>|bool> $rules
     */
    #[DataProvider('getDataForTestCalculate')]
    public function testCalculate(int $expectedHash, array $rules, bool $isRiskyAllowed): void
    {
        $config = $this->createMock(Config::class);
        $config->method('getRiskyAllowed')->willReturn($isRiskyAllowed);
        $config->method('getRules')->willReturn($rules);
        $calculator = new ConfigHashCalculator();

        self::assertSame($expectedHash, $calculator->calculate($config));
    }

    /**
     * @return iterable<array{0: int, 1: array<string, array<string, mixed>|bool>, 2: bool}>
     */
    public static function getDataForTestCalculate(): iterable
    {
        // take into account if risky rules is allowed
        yield [1105664888, ['some_rule' => true], false];
        yield [2812029674, ['some_rule' => true], true];

        // never mined rules order
        yield [269056745, ['rule_a' => true, 'rule_b' => true], false];
        yield [269056745, ['rule_b' => true, 'rule_a' => true], false];
    }
}
