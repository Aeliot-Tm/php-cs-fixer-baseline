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

use Aeliot\PhpCsFixerBaseline\Model\BuilderConfig;
use Aeliot\PhpCsFixerBaseline\Service\InvalidFilesDetector;
use Aeliot\PhpCsFixerBaseline\Service\PathNormalizer;
use Aeliot\PhpCsFixerBaseline\Service\PhpCsFixerBinaryResolver;
use Aeliot\PhpCsFixerBaseline\Service\VendorPathResolver;
use PhpCsFixer\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidFilesDetector::class)]
final class InvalidFilesDetectorTest extends TestCase
{
    private string $projectRoot;

    protected function setUp(): void
    {
        $this->projectRoot = \dirname(__DIR__, 3);
    }

    public function testDetectReturnsOnlyNonCompliantFiles(): void
    {
        /** @var Config $config */
        $config = require $this->projectRoot . '/tests/config/.php-cs-fixer-detector.php';

        /** @var \PhpCsFixer\Finder $finder */
        $finder = require $this->projectRoot . '/tests/config/.php-cs-fixer-finder-invalid-only.php';

        $builderConfig = new BuilderConfig([
            'baselinePath' => $this->projectRoot . '/tests/config/.php-cs-fixer-baseline.json',
            'config' => $config,
            'configPath' => $this->projectRoot . '/tests/config/.php-cs-fixer-detector.php',
            'finder' => $finder,
            'invalidOnly' => true,
            'relative' => true,
            'workdir' => $this->projectRoot,
        ]);

        $pathNormalizer = new PathNormalizer();

        $detectedPaths = (new InvalidFilesDetector(
            new PhpCsFixerBinaryResolver(new VendorPathResolver()),
            $pathNormalizer,
        ))->detect($builderConfig);

        $compliantPaths = [
            $pathNormalizer->normalize($this->projectRoot . '/tests/fixtures/invalid-only/compliant-first.php'),
            $pathNormalizer->normalize($this->projectRoot . '/tests/fixtures/invalid-only/compliant-second.php'),
        ];
        $nonCompliantPaths = [
            $pathNormalizer->normalize($this->projectRoot . '/tests/fixtures/invalid-only/non-compliant-first.php'),
            $pathNormalizer->normalize($this->projectRoot . '/tests/fixtures/invalid-only/non-compliant-second.php'),
        ];

        foreach ($compliantPaths as $compliantPath) {
            self::assertArrayNotHasKey($compliantPath, $detectedPaths);
        }

        foreach ($nonCompliantPaths as $nonCompliantPath) {
            self::assertArrayHasKey($nonCompliantPath, $detectedPaths);
        }

        self::assertCount(2, $detectedPaths);
    }
}
