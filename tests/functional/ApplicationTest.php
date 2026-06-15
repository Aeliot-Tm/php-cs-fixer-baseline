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

namespace Aeliot\PhpCsFixerBaseline\Test\Functional;

use Aeliot\PhpCsFixerBaseline\Console\Application;
use Aeliot\PhpCsFixerBaseline\Console\Command\GenerateCommand;
use Aeliot\PhpCsFixerBaseline\Console\Command\UpdateCommand;
use Aeliot\PhpCsFixerBaseline\Console\ContainerBuilder;
use Aeliot\PhpCsFixerBaseline\Service\FileCacheCalculator;
use Aeliot\PhpCsFixerBaseline\Service\FilterFactory;
use PhpCsFixer\Config;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @coversNothing
 * @large
 */
final class ApplicationTest extends TestCase
{
    private string $projectRoot;

    private string $baselinePath;
    private string $firstFixtureFile;
    private string $firstFixtureOriginalContent;
    private string $secondFixtureFile;
    private string $secondFixtureOriginalContent;

    protected function setUp(): void
    {
        $this->projectRoot = \dirname(__DIR__, 2);
        $this->baselinePath = $this->projectRoot . '/tests/config/.php-cs-fixer-baseline.json';
        $this->firstFixtureFile = $this->projectRoot . '/tests/fixtures/file-for-calculation-of-hash.php';
        $this->firstFixtureOriginalContent = (string) file_get_contents($this->firstFixtureFile);
        $this->secondFixtureFile = $this->projectRoot . '/tests/fixtures/file-for-calculation-of-hash-second.php';
        $this->secondFixtureOriginalContent = (string) file_get_contents($this->secondFixtureFile);

        if (file_exists($this->baselinePath)) {
            unlink($this->baselinePath);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->baselinePath)) {
            unlink($this->baselinePath);
        }

        file_put_contents($this->firstFixtureFile, $this->firstFixtureOriginalContent);
        file_put_contents($this->secondFixtureFile, $this->secondFixtureOriginalContent);
    }

    public function testContainerBuildsAndProvidesGenerateCommand(): void
    {
        $container = ContainerBuilder::build();

        self::assertTrue($container->has(GenerateCommand::class));
        self::assertInstanceOf(GenerateCommand::class, $container->get(GenerateCommand::class));
    }

    public function testGenerateCommandRunsSuccessfully(): void
    {
        [$exitCode, $output] = $this->runGenerateCommand();

        self::assertSame(0, $exitCode);
        self::assertFileExists($this->baselinePath);
        self::assertStringContainsString('Ok, 2 files added to baseline', $output);

        $content = json_decode((string) file_get_contents($this->baselinePath), true, 512, \JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('config_hash', $content);
        self::assertArrayHasKey('hashes', $content);
        self::assertCount(2, $content['hashes']);
    }

    public function testContainerBuildsAndProvidesUpdateCommand(): void
    {
        $container = ContainerBuilder::build();

        self::assertTrue($container->has(UpdateCommand::class));
        self::assertInstanceOf(UpdateCommand::class, $container->get(UpdateCommand::class));
    }

    public function testUpdateCommandRunsSuccessfully(): void
    {
        $this->runGenerateCommand();
        file_put_contents($this->firstFixtureFile, $this->firstFixtureOriginalContent . "\n");

        [$exitCode, $output] = $this->runUpdateCommand([$this->firstFixtureFile]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('Ok, 1 file(s) updated in baseline', $output);

        /** @var Config $config */
        $config = require $this->projectRoot . '/tests/config/.php-cs-fixer.dist.php';

        $filter = (new FilterFactory())->createFilter($this->baselinePath, $config);
        $file = new \SplFileInfo((string) realpath($this->firstFixtureFile));

        self::assertFalse($filter($file));
    }

    public function testUpdateCommandUpdatesOnlyRequestedFile(): void
    {
        $this->runGenerateCommand();
        $baselineBeforeUpdate = $this->readBaselineHashes();
        $secondFileBaselineKey = 'tests/fixtures/file-for-calculation-of-hash-second.php';
        self::assertArrayHasKey($secondFileBaselineKey, $baselineBeforeUpdate);

        file_put_contents($this->firstFixtureFile, $this->firstFixtureOriginalContent . "\n// changed first\n");
        file_put_contents($this->secondFixtureFile, $this->secondFixtureOriginalContent . "\n// changed second\n");

        [$exitCode, $output] = $this->runUpdateCommand([$this->firstFixtureFile]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('Ok, 1 file(s) updated in baseline', $output);

        $baselineAfterUpdate = $this->readBaselineHashes();
        $calculator = new FileCacheCalculator();

        self::assertSame(
            $calculator->calculate(new \SplFileInfo((string) realpath($this->firstFixtureFile))),
            $baselineAfterUpdate['tests/fixtures/file-for-calculation-of-hash.php'],
        );
        self::assertSame(
            $baselineBeforeUpdate[$secondFileBaselineKey],
            $baselineAfterUpdate[$secondFileBaselineKey],
        );
        self::assertNotSame(
            $calculator->calculate(new \SplFileInfo((string) realpath($this->secondFixtureFile))),
            $baselineAfterUpdate[$secondFileBaselineKey],
        );
    }

    public function testUpdateCommandUpdatesTwoFilesInOneCall(): void
    {
        $this->runGenerateCommand();
        $baselineBeforeUpdate = $this->readBaselineHashes();

        file_put_contents($this->firstFixtureFile, $this->firstFixtureOriginalContent . "\n// changed first\n");
        file_put_contents($this->secondFixtureFile, $this->secondFixtureOriginalContent . "\n// changed second\n");

        [$exitCode, $output] = $this->runUpdateCommand([$this->firstFixtureFile, $this->secondFixtureFile]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('Ok, 2 file(s) updated in baseline', $output);

        $baselineAfterUpdate = $this->readBaselineHashes();
        $calculator = new FileCacheCalculator();

        self::assertSame(
            $calculator->calculate(new \SplFileInfo((string) realpath($this->firstFixtureFile))),
            $baselineAfterUpdate['tests/fixtures/file-for-calculation-of-hash.php'],
        );
        self::assertSame(
            $calculator->calculate(new \SplFileInfo((string) realpath($this->secondFixtureFile))),
            $baselineAfterUpdate['tests/fixtures/file-for-calculation-of-hash-second.php'],
        );
        self::assertNotSame(
            $baselineBeforeUpdate['tests/fixtures/file-for-calculation-of-hash.php'],
            $baselineAfterUpdate['tests/fixtures/file-for-calculation-of-hash.php'],
        );
        self::assertNotSame(
            $baselineBeforeUpdate['tests/fixtures/file-for-calculation-of-hash-second.php'],
            $baselineAfterUpdate['tests/fixtures/file-for-calculation-of-hash-second.php'],
        );
    }

    public function testFilterFactoryExcludesBaselineFileWhenConfigMatches(): void
    {
        $this->runGenerateCommand();

        /** @var Config $config */
        $config = require $this->projectRoot . '/tests/config/.php-cs-fixer.dist.php';

        $filter = (new FilterFactory())->createFilter($this->baselinePath, $config);
        $file = new \SplFileInfo(
            (string) realpath($this->projectRoot . '/tests/fixtures/file-for-calculation-of-hash.php'),
        );

        self::assertFalse($filter($file));
    }

    public function testFilterFactoryIncludesBaselineFileWhenConfigDiffers(): void
    {
        $baselineFixturePath = $this->projectRoot . '/tests/fixtures/.php-cs-fixer-baseline.json';
        $config = $this->createMock(Config::class);
        $config->method('getRiskyAllowed')->willReturn(true);
        $config->method('getRules')->willReturn(['some_new_rule' => true]);

        $filter = (new FilterFactory())->createFilter($baselineFixturePath, $config);
        $file = new \SplFileInfo(
            (string) realpath($this->projectRoot . '/tests/fixtures/file-for-calculation-of-hash.php'),
        );

        self::assertTrue($filter($file));
    }

    public function testGenerateCommandWithInvalidOnlyIncludesOnlyNonCompliantFiles(): void
    {
        [$exitCode, $output] = $this->runGenerateCommand([
            '--config' => '.php-cs-fixer-detector.php',
            '--finder' => '.php-cs-fixer-finder-invalid-only.php',
            '--invalid-only' => true,
            '--workdir' => $this->projectRoot,
        ]);

        self::assertSame(0, $exitCode);
        self::assertFileExists($this->baselinePath);
        self::assertStringContainsString('Ok, 2 files added to baseline', $output);

        $content = json_decode((string) file_get_contents($this->baselinePath), true, 512, \JSON_THROW_ON_ERROR);
        self::assertCount(2, $content['hashes']);

        $compliantPaths = [
            'tests/fixtures/invalid-only/compliant-first.php',
            'tests/fixtures/invalid-only/compliant-second.php',
        ];
        $nonCompliantPaths = [
            'tests/fixtures/invalid-only/non-compliant-first.php',
            'tests/fixtures/invalid-only/non-compliant-second.php',
        ];

        foreach ($compliantPaths as $compliantPath) {
            self::assertArrayNotHasKey($compliantPath, $content['hashes']);
        }

        foreach ($nonCompliantPaths as $nonCompliantPath) {
            self::assertArrayHasKey($nonCompliantPath, $content['hashes']);
        }
    }

    /**
     * @return array<string, int>
     */
    private function readBaselineHashes(): array
    {
        $content = json_decode((string) file_get_contents($this->baselinePath), true, 512, \JSON_THROW_ON_ERROR);

        return array_map(
            static fn (array $entry): int => $entry['hash'],
            $content['hashes'],
        );
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array{0: int, 1: string}
     */
    private function runGenerateCommand(array $options = []): array
    {
        $container = ContainerBuilder::build();
        $application = new Application($container);
        $application->setAutoExit(false);
        $application->setDefaultCommand('generate', true);

        $output = new BufferedOutput();
        $exitCode = $application->run(
            new ArrayInput(array_merge(['--config-dir' => 'tests/config/'], $options)),
            $output,
        );

        return [$exitCode, $output->fetch()];
    }

    /**
     * @param list<string> $paths
     *
     * @return array{0: int, 1: string}
     */
    private function runUpdateCommand(array $paths): array
    {
        $container = ContainerBuilder::build();
        $application = new Application($container);
        $application->setAutoExit(false);

        $output = new BufferedOutput();
        $exitCode = $application->run(
            new ArrayInput([
                'command' => 'update',
                '--config-dir' => 'tests/config/',
                'path' => $paths,
            ]),
            $output,
        );

        return [$exitCode, $output->fetch()];
    }
}
