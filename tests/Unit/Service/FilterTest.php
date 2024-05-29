<?php

declare(strict_types=1);

namespace Aeliot\PhpCsFixerBaseline\Test\Unit\Service;

use Aeliot\PhpCsFixerBaseline\Service\FilterFactory;
use PhpCsFixer\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilterFactory::class)]
final class FilterTest extends TestCase
{
    public function testFilterWithSameConfig(): void
    {
        $path = __DIR__ . '/../../fixtures/.php-cs-fixer-baseline.json';
        $config = $this->createMock(Config::class);
        $config->method('getRiskyAllowed')->willReturn(true);
        $rules = [
            '@Symfony' => true,
            '@Symfony:risky' => true,
            'concat_space' => [
                'spacing' => 'one',
            ],
            'phpdoc_align' => ['align' => 'left'],
        ];
        $config->method('getRules')->willReturn($rules);

        $filter = (new FilterFactory())->createFilter($path, $config);

        $file = $this->createMock(\SplFileInfo::class);
        $file
            ->method('getPathname')
            ->willReturn(realpath(__DIR__ . '/../../fixtures/file-for-calculation-of-hash.php'));

        self::assertFalse($filter($file));
    }

    public function testFilterWithAnotherConfig(): void
    {
        $path = __DIR__ . '/../../fixtures/.php-cs-fixer-baseline.json';
        $config = $this->createMock(Config::class);
        $config->method('getRiskyAllowed')->willReturn(true);
        $rules = ['some_new_rule' => true];
        $config->method('getRules')->willReturn($rules);

        $filter = (new FilterFactory())->createFilter($path, $config);

        $file = $this->createMock(\SplFileInfo::class);
        $file
            ->method('getPathname')
            ->willReturn(realpath(__DIR__ . '/../../fixtures/file-for-calculation-of-hash.php'));

        self::assertTrue($filter($file));
    }
}
