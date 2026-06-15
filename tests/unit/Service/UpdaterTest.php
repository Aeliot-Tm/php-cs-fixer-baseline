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

use Aeliot\PhpCsFixerBaseline\Exception\InvalidArgumentException;
use Aeliot\PhpCsFixerBaseline\Service\FileCacheCalculator;
use Aeliot\PhpCsFixerBaseline\Service\Reader;
use Aeliot\PhpCsFixerBaseline\Service\Updater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Updater::class)]
final class UpdaterTest extends TestCase
{
    private string $fixtureFile;

    /** @var list<string> */
    private array $temporaryFiles = [];

    protected function setUp(): void
    {
        $this->fixtureFile = (string) realpath(__DIR__ . '/../../fixtures/file-for-calculation-of-hash.php');
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $temporaryFile) {
            if (file_exists($temporaryFile)) {
                unlink($temporaryFile);
            }
        }
    }

    public function testUpdateOneFileInAbsoluteBaseline(): void
    {
        $baselinePath = $this->createTemporaryBaseline([
            'config_hash' => 1624530864,
            'relative' => false,
            'hashes' => [
                $this->fixtureFile => ['hash' => 0],
            ],
        ]);
        $originalContent = (string) file_get_contents($this->fixtureFile);
        file_put_contents($this->fixtureFile, $originalContent . "\n");

        try {
            $baselineFile = (new Updater(new Reader(), new FileCacheCalculator()))->update(
                [
                    'baselinePath' => $baselinePath,
                    'relative' => false,
                    'workdir' => null,
                ],
                [$this->fixtureFile],
            );

            $fileHash = $baselineFile->getContent()->getHash($this->fixtureFile);
            self::assertNotNull($fileHash);
            self::assertSame(
                (new FileCacheCalculator())->calculate(new \SplFileInfo($this->fixtureFile)),
                $fileHash->getHash(),
            );
            self::assertSame(1624530864, $baselineFile->getContent()->getConfigHash());
        } finally {
            file_put_contents($this->fixtureFile, $originalContent);
        }
    }

    public function testUpdateTwoFilesInOneCall(): void
    {
        $firstFile = $this->createTemporaryPhpFile('first');
        $secondFile = $this->createTemporaryPhpFile('second');
        $calculator = new FileCacheCalculator();
        $baselinePath = $this->createTemporaryBaseline([
            'config_hash' => 1,
            'relative' => false,
            'hashes' => [
                $firstFile => ['hash' => 0],
                $secondFile => ['hash' => 0],
            ],
        ]);

        file_put_contents($firstFile, "<?php\n// changed\n");
        file_put_contents($secondFile, "<?php\n// changed too\n");

        $baselineFile = (new Updater(new Reader(), new FileCacheCalculator()))->update(
            [
                'baselinePath' => $baselinePath,
                'relative' => false,
                'workdir' => null,
            ],
            [$firstFile, $secondFile],
        );

        self::assertSame(2, $baselineFile->getContent()->getHashesCount());
        self::assertSame(
            $calculator->calculate(new \SplFileInfo($firstFile)),
            $baselineFile->getContent()->getHash($firstFile)?->getHash(),
        );
        self::assertSame(
            $calculator->calculate(new \SplFileInfo($secondFile)),
            $baselineFile->getContent()->getHash($secondFile)?->getHash(),
        );
    }

    public function testOverwriteExistingEntryDoesNotIncreaseCount(): void
    {
        $baselinePath = $this->createTemporaryBaseline([
            'config_hash' => 1,
            'relative' => false,
            'hashes' => [
                $this->fixtureFile => ['hash' => 0],
            ],
        ]);
        $originalContent = (string) file_get_contents($this->fixtureFile);
        file_put_contents($this->fixtureFile, $originalContent . "\n");

        try {
            $baselineFile = (new Updater(new Reader(), new FileCacheCalculator()))->update(
                [
                    'baselinePath' => $baselinePath,
                    'relative' => false,
                    'workdir' => null,
                ],
                [$this->fixtureFile],
            );

            self::assertSame(1, $baselineFile->getContent()->getHashesCount());
        } finally {
            file_put_contents($this->fixtureFile, $originalContent);
        }
    }

    public function testFileNotInBaselineThrowsException(): void
    {
        $baselinePath = $this->createTemporaryBaseline([
            'config_hash' => 1,
            'relative' => false,
            'hashes' => [],
        ]);
        $anotherFile = $this->createTemporaryPhpFile('outside-baseline');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('is not in baseline');

        (new Updater(new Reader(), new FileCacheCalculator()))->update(
            [
                'baselinePath' => $baselinePath,
                'relative' => false,
                'workdir' => null,
            ],
            [$anotherFile],
        );
    }

    public function testRelativeBaselineWithWorkdir(): void
    {
        $projectRoot = \dirname(__DIR__, 3);
        $relativePath = 'tests/fixtures/file-for-calculation-of-hash.php';
        $baselinePath = $this->createTemporaryBaseline([
            'config_hash' => 1,
            'relative' => true,
            'hashes' => [
                $relativePath => ['hash' => 0],
            ],
        ]);
        $originalContent = (string) file_get_contents($this->fixtureFile);
        file_put_contents($this->fixtureFile, $originalContent . "\n");

        try {
            $baselineFile = (new Updater(new Reader(), new FileCacheCalculator()))->update(
                [
                    'baselinePath' => $baselinePath,
                    'relative' => true,
                    'workdir' => $projectRoot,
                ],
                [$this->fixtureFile],
            );

            $fileHash = $baselineFile->getContent()->getHash($this->fixtureFile);
            self::assertNotNull($fileHash);
            self::assertSame($relativePath, $fileHash->getPath());
        } finally {
            file_put_contents($this->fixtureFile, $originalContent);
        }
    }

    public function testNonExistentFileThrowsException(): void
    {
        $baselinePath = $this->createTemporaryBaseline([
            'config_hash' => 1,
            'relative' => false,
            'hashes' => [
                $this->fixtureFile => ['hash' => 4266623405],
            ],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not exist');

        (new Updater(new Reader(), new FileCacheCalculator()))->update(
            [
                'baselinePath' => $baselinePath,
                'relative' => false,
                'workdir' => null,
            ],
            ['/path/to/non-existent-file.php'],
        );
    }

    public function testNonExistentBaselineThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Baseline file');

        (new Updater(new Reader(), new FileCacheCalculator()))->update(
            [
                'baselinePath' => sys_get_temp_dir() . '/missing-baseline.json',
                'relative' => false,
                'workdir' => null,
            ],
            [$this->fixtureFile],
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createTemporaryBaseline(array $data): string
    {
        $path = sys_get_temp_dir() . '/pcsf-baseline-' . uniqid('', true) . '.json';
        $this->temporaryFiles[] = $path;
        file_put_contents($path, (string) json_encode($data, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT));

        return $path;
    }

    private function createTemporaryPhpFile(string $suffix): string
    {
        $path = sys_get_temp_dir() . '/pcsf-file-' . $suffix . '-' . uniqid('', true) . '.php';
        $this->temporaryFiles[] = $path;
        file_put_contents($path, "<?php\n// {$suffix}\n");

        return $path;
    }
}
