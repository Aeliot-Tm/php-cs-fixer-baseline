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
use Aeliot\PhpCsFixerBaseline\Console\ContainerBuilder;
use Aeliot\PhpCsFixerBaseline\Service\FilterFactory;
use PhpCsFixer\Config;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversNothing]
#[Large]
final class ApplicationTest extends TestCase
{
    private string $projectRoot;

    private string $baselinePath;

    protected function setUp(): void
    {
        $this->projectRoot = \dirname(__DIR__, 2);
        $this->baselinePath = $this->projectRoot . '/tests/config/.php-cs-fixer-baseline.json';

        if (file_exists($this->baselinePath)) {
            unlink($this->baselinePath);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->baselinePath)) {
            unlink($this->baselinePath);
        }
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
        self::assertStringContainsString('Ok, 1 files added to baseline', $output);

        $content = json_decode((string) file_get_contents($this->baselinePath), true, 512, \JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('config_hash', $content);
        self::assertArrayHasKey('hashes', $content);
        self::assertCount(1, $content['hashes']);
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

    /**
     * @return array{0: int, 1: string}
     */
    private function runGenerateCommand(): array
    {
        $container = ContainerBuilder::build();
        $application = new Application($container);
        $application->setAutoExit(false);
        $application->setDefaultCommand('generate', true);

        $output = new BufferedOutput();
        $exitCode = $application->run(
            new ArrayInput(['--config-dir' => 'tests/config/']),
            $output,
        );

        return [$exitCode, $output->fetch()];
    }
}
