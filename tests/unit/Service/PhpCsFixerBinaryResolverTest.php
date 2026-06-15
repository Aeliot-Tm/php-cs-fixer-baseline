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

use Aeliot\PhpCsFixerBaseline\Service\PhpCsFixerBinaryResolver;
use Aeliot\PhpCsFixerBaseline\Service\VendorPathResolver;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Aeliot\PhpCsFixerBaseline\Service\PhpCsFixerBinaryResolver
 */
final class PhpCsFixerBinaryResolverTest extends TestCase
{
    /** @var array<string, string|false> */
    private array $originalEnv;

    protected function setUp(): void
    {
        $this->originalEnv = [
            'PHP_CS_FIXER_BINARY' => getenv('PHP_CS_FIXER_BINARY'),
        ];
        unset($GLOBALS['_composer_autoload_path']);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['_composer_autoload_path']);
        $this->restoreEnv('PHP_CS_FIXER_BINARY', $this->originalEnv['PHP_CS_FIXER_BINARY']);
    }

    public function testResolveUsesEnvironmentVariable(): void
    {
        putenv('PHP_CS_FIXER_BINARY=/custom/php-cs-fixer');

        $resolver = new PhpCsFixerBinaryResolver(new VendorPathResolver());

        self::assertSame('/custom/php-cs-fixer', $resolver->resolve());
    }

    public function testResolvePrefersVendorBinPhpCsFixer(): void
    {
        $root = $this->createProjectLayout();
        $GLOBALS['_composer_autoload_path'] = $root . '/vendor-bin/pcsf-baseline/vendor/autoload.php';

        $resolver = new PhpCsFixerBinaryResolver(new VendorPathResolver());

        self::assertSame(
            realpath($root . '/vendor-bin/pcsf-baseline/vendor/bin/php-cs-fixer'),
            $resolver->resolve(),
        );
    }

    public function testResolveUsesStandardVendorBinary(): void
    {
        $root = $this->createProjectLayout(includeVendorBinBinary: false);
        $GLOBALS['_composer_autoload_path'] = $root . '/vendor/autoload.php';

        $resolver = new PhpCsFixerBinaryResolver(new VendorPathResolver());

        self::assertSame(realpath($root . '/vendor/bin/php-cs-fixer'), $resolver->resolve());
    }

    private function createProjectLayout(bool $includeVendorBinBinary = true): string
    {
        $root = sys_get_temp_dir() . '/pcsf-baseline-' . uniqid('', true);
        $paths = [
            $root . '/vendor-bin/pcsf-baseline/vendor/bin',
            $root . '/vendor/bin',
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
        file_put_contents($root . '/vendor/bin/php-cs-fixer', "#!/usr/bin/env php\n");
        chmod($root . '/vendor/bin/php-cs-fixer', 0755);

        if ($includeVendorBinBinary) {
            file_put_contents($root . '/vendor-bin/pcsf-baseline/vendor/bin/php-cs-fixer', "#!/usr/bin/env php\n");
            chmod($root . '/vendor-bin/pcsf-baseline/vendor/bin/php-cs-fixer', 0755);
        }

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

    private function restoreEnv(string $name, string|false $value): void
    {
        if (false === $value) {
            putenv($name);

            return;
        }

        putenv($name . '=' . $value);
    }
}
