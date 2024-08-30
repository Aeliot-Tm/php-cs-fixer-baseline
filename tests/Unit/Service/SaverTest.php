<?php

declare(strict_types=1);

/*
 * This file is part of the box project.
 *
 * (c) Anatoliy Melnikov <5785276@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Aeliot\PhpCsFixerBaseline\Test\Unit\Service;

use Aeliot\PhpCsFixerBaseline\Model\BaselineFile;
use Aeliot\PhpCsFixerBaseline\Service\Reader;
use Aeliot\PhpCsFixerBaseline\Service\Saver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Saver::class)]
final class SaverTest extends TestCase
{
    public function testSave(): void
    {
        $pathTMP = sys_get_temp_dir() . '/pcsf-baseline-' . date('YmdHis') . '-' . random_int(0, 9999) . '.json';
        $path = __DIR__ . '/../../fixtures/.php-cs-fixer-baseline.json';

        /** @phpstan-ignore-next-line */
        $baselineFile = $this->createMock(BaselineFile::class);
        $baselineFile->method('getPath')->willReturn($pathTMP);
        $baselineFile->method('getContent')->willReturn((new Reader())->read($path)->getContent());

        (new Saver())->save($baselineFile);

        self::assertFileEquals($path, $pathTMP);

        unlink($pathTMP);
    }
}
