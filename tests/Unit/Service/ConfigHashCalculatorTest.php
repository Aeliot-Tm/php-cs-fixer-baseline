<?php

declare(strict_types=1);

namespace Aeliot\PhpCsFixerBaseline\Test\Unit\Service;

use Aeliot\PhpCsFixerBaseline\Service\ConfigHashCalculator;
use PhpCsFixer\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigHashCalculator::class)]
final class ConfigHashCalculatorTest extends TestCase
{
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
     * @return iterable<array{0: int, 1: array<string,mixed>, 2: bool}>
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
