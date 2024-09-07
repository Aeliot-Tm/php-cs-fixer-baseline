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

use Aeliot\PhpCsFixerBaseline\Service\FilterFactory;
use PhpCsFixer\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilterFactory::class)]
final class FilterTest extends TestCase
{
    public function testFilterWithSameConfig(): void
    {
        $this->markTestIncomplete('Test fallen on CI. Soon of it is depends on EOL config of git');

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
