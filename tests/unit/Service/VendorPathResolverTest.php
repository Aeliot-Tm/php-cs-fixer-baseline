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

use Aeliot\PhpCsFixerBaseline\Exception\RuntimeException;
use Aeliot\PhpCsFixerBaseline\Service\VendorPathResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VendorPathResolver::class)]
final class VendorPathResolverTest extends TestCase
{
    private VendorPathResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new VendorPathResolver();
        unset($GLOBALS['_composer_autoload_path']);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['_composer_autoload_path']);
    }

    public function testResolveAutoloaderPathUsesComposerGlobalWhenSet(): void
    {
        $root = $this->createProjectLayout();
        $autoloadPath = $root . '/vendor-bin/pcsf-baseline/vendor/autoload.php';
        $binaryDirectory = $root . '/vendor-bin/pcsf-baseline/vendor/aeliot/php-cs-fixer-baseline/bin';

        $GLOBALS['_composer_autoload_path'] = $autoloadPath;

        self::assertSame($autoloadPath, $this->resolver->resolveAutoloaderPath($binaryDirectory));
    }

    public function testResolveAutoloaderPathFallsBackToVendorBinLayout(): void
    {
        $root = $this->createProjectLayout();
        $binaryDirectory = $root . '/vendor-bin/pcsf-baseline/vendor/aeliot/php-cs-fixer-baseline/bin';

        self::assertSame(
            realpath($root . '/vendor-bin/pcsf-baseline/vendor/autoload.php'),
            $this->resolver->resolveAutoloaderPath($binaryDirectory),
        );
    }

    public function testResolveAutoloaderPathFallsBackToStandardVendorLayout(): void
    {
        $root = $this->createProjectLayout();
        $binaryDirectory = $root . '/vendor/aeliot/php-cs-fixer-baseline/bin';

        self::assertSame(
            realpath($root . '/vendor/autoload.php'),
            $this->resolver->resolveAutoloaderPath($binaryDirectory),
        );
    }

    public function testResolveAutoloaderPathFallsBackToPackageVendorDuringDevelopment(): void
    {
        $root = sys_get_temp_dir() . '/pcsf-baseline-' . uniqid('', true);
        mkdir($root . '/bin', 0777, true);
        mkdir($root . '/vendor', 0777, true);
        file_put_contents($root . '/vendor/autoload.php', '<?php');
        $this->registerProjectCleanup($root);

        self::assertSame(
            realpath($root . '/vendor/autoload.php'),
            $this->resolver->resolveAutoloaderPath($root . '/bin'),
        );
    }

    public function testResolveAutoloaderPathThrowsWhenAutoloaderIsMissing(): void
    {
        $root = sys_get_temp_dir() . '/pcsf-baseline-' . uniqid('', true);
        mkdir($root . '/bin', 0777, true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot find autoloader');

        try {
            $this->resolver->resolveAutoloaderPath($root . '/bin');
        } finally {
            rmdir($root . '/bin');
            rmdir($root);
        }
    }

    public function testGetVendorRootsPrefersComposerAutoloadRoot(): void
    {
        $root = $this->createProjectLayout();
        $packageRoot = $root . '/vendor-bin/pcsf-baseline/vendor/aeliot/php-cs-fixer-baseline';
        $GLOBALS['_composer_autoload_path'] = $root . '/vendor-bin/pcsf-baseline/vendor/autoload.php';

        self::assertSame(
            [
                $root . '/vendor-bin/pcsf-baseline',
                $packageRoot,
            ],
            $this->resolver->getVendorRoots($packageRoot),
        );
    }

    public function testGetVendorRootsReturnsPackageRootWhenComposerGlobalIsMissing(): void
    {
        $packageRoot = '/tmp/project/vendor/aeliot/php-cs-fixer-baseline';

        self::assertSame([$packageRoot], $this->resolver->getVendorRoots($packageRoot));
    }

    private function createProjectLayout(): string
    {
        $root = sys_get_temp_dir() . '/pcsf-baseline-' . uniqid('', true);

        $paths = [
            $root . '/vendor-bin/pcsf-baseline/vendor/aeliot/php-cs-fixer-baseline/bin',
            $root . '/vendor/aeliot/php-cs-fixer-baseline/bin',
            $root . '/bin',
            $root . '/vendor-bin/pcsf-baseline/vendor',
            $root . '/vendor',
        ];

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }

        file_put_contents($root . '/vendor-bin/pcsf-baseline/vendor/autoload.php', '<?php');
        file_put_contents($root . '/vendor/autoload.php', '<?php');

        $this->registerProjectCleanup($root);

        return $root;
    }

    private function registerProjectCleanup(string $root): void
    {
        $this->addToAssertionCount(0);

        register_shutdown_function(static function () use ($root): void {
            if (!is_dir($root)) {
                return;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST,
            );

            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isDir()) {
                    rmdir($fileInfo->getPathname());

                    continue;
                }

                unlink($fileInfo->getPathname());
            }

            rmdir($root);
        });
    }
}
