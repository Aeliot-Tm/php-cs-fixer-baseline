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

namespace Aeliot\PhpCsFixerBaseline\Service;

use Aeliot\PhpCsFixerBaseline\Exception\RuntimeException;
use Aeliot\PhpCsFixerBaseline\Model\BuilderConfig;
use PhpCsFixer\Finder;

final class InvalidFilesDetector
{
    public function __construct(
        private readonly PhpCsFixerBinaryResolver $binaryResolver,
        private readonly PathNormalizer $pathNormalizer,
    ) {
    }

    /**
     * @return array<string, true>
     */
    public function detect(BuilderConfig $config): array
    {
        $workdir = $config->getWorkdir() ?? getcwd();
        if (false === $workdir) {
            throw new RuntimeException('Unable to resolve working directory.');
        }

        $filePaths = $this->collectFilePaths($config->getFinder(), $workdir);
        if ([] === $filePaths) {
            return [];
        }

        $output = $this->runCheck(
            $workdir,
            $config->getConfigPath(),
            $config->getConfig()->getRiskyAllowed(),
            $filePaths,
        );

        return $this->parseReport($output, $workdir);
    }

    /**
     * @param list<string> $filePaths
     */
    private function runCheck(
        string $workdir,
        string $configPath,
        bool $riskyAllowed,
        array $filePaths,
    ): string {
        $command = [
            \PHP_BINARY,
            $this->binaryResolver->resolve(),
            'fix',
            '--dry-run',
            '--config=' . $configPath,
            '--using-cache=no',
            '--format=json',
            '--show-progress=none',
            '--allow-risky=' . ($riskyAllowed ? 'yes' : 'no'),
            '--path-mode=override',
            ...$filePaths,
        ];

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes, $workdir);
        if (!\is_resource($process)) {
            throw new RuntimeException('Failed to start php-cs-fixer process.');
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if (!\in_array($exitCode, [0, 8], true)) {
            throw new RuntimeException(\sprintf('php-cs-fixer check failed with exit code %d: %s', $exitCode, trim((string) $stderr)));
        }

        if (false === $stdout || '' === trim($stdout)) {
            throw new RuntimeException('php-cs-fixer check returned empty output.');
        }

        return $stdout;
    }

    /**
     * @return array<string, true>
     */
    private function parseReport(string $output, string $workdir): array
    {
        /** @var array{files?: list<array{name?: string}>} $report */
        $report = json_decode($output, true, 512, \JSON_THROW_ON_ERROR);

        $normalizedPaths = [];

        foreach ($report['files'] ?? [] as $file) {
            if (!isset($file['name']) || !\is_string($file['name'])) {
                continue;
            }

            $normalizedPaths[$this->pathNormalizer->normalize($file['name'], $workdir)] = true;
        }

        return $normalizedPaths;
    }

    /**
     * @return list<string>
     */
    private function collectFilePaths(Finder $finder, string $workdir): array
    {
        $paths = [];
        $normalizedWorkdir = $this->pathNormalizer->normalize($workdir);

        foreach ($finder as $file) {
            $realPath = $file->getRealPath();
            if (false === $realPath) {
                continue;
            }

            $normalizedPath = $this->pathNormalizer->normalize($realPath);
            if (str_starts_with($normalizedPath, $normalizedWorkdir . '/')) {
                $paths[] = substr($normalizedPath, \strlen($normalizedWorkdir) + 1);

                continue;
            }

            $paths[] = $normalizedPath;
        }

        return $paths;
    }
}
