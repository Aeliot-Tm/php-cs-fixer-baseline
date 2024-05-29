<?php

declare(strict_types=1);

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

        $baselineFile = $this->createMock(BaselineFile::class);
        $baselineFile->method('getPath')->willReturn($pathTMP);
        $baselineFile->method('getContent')->willReturn((new Reader())->read($path)->getContent());

        (new Saver())->save($baselineFile);

        self::assertFileEquals($path, $pathTMP);

        unlink($pathTMP);
    }
}
