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
        private PhpCsFixerBinaryResolver $binaryResolver,
        private PathNormalizer $pathNormalizer,
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

        $workdir = $this->pathNormalizer->normalize($workdir);
        $filePaths = $this->collectFilePaths($config->getFinder(), $workdir);
        if ([] === $filePaths) {
            return [];
        }

        $output = $this->runCheck(
            $workdir,
            $this->pathNormalizer->normalize($config->getConfigPath()),
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

        if ($this->supportsSequentialFlag()) {
            $command[] = '--sequential';
        }

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

        $output = $this->extractJsonOutput((string) $stdout, (string) $stderr);
        if ('' === $output) {
            throw new RuntimeException(\sprintf('php-cs-fixer check returned empty JSON output. stdout: %s stderr: %s', trim((string) $stdout), trim((string) $stderr)));
        }

        return $output;
    }

    private function supportsSequentialFlag(): bool
    {
        $command = [
            \PHP_BINARY,
            $this->binaryResolver->resolve(),
            '--version',
        ];

        $versionOutput = $this->runCommandOutput($command);
        if (null === $versionOutput) {
            return false;
        }

        if (!preg_match('/(\d+)\.(\d+)\.(\d+)/', $versionOutput, $matches)) {
            return false;
        }

        return version_compare($matches[1] . '.' . $matches[2] . '.' . $matches[3], '3.50.0', '>=');
    }

    /**
     * @param list<string> $command
     */
    private function runCommandOutput(array $command): ?string
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes);
        if (!\is_resource($process)) {
            return null;
        }

        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        return trim($output);
    }

    private function extractJsonOutput(string $stdout, string $stderr): string
    {
        foreach ([$stdout, $stderr, $stdout . "\n" . $stderr] as $stream) {
            foreach (array_reverse(explode("\n", trim($stream))) as $line) {
                $line = trim($line);
                if ('' === $line || !str_starts_with($line, '{')) {
                    continue;
                }

                if (str_contains($line, '"files"')) {
                    return $line;
                }
            }
        }

        return '';
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

        foreach ($finder as $file) {
            $paths[] = $this->pathNormalizer->normalizeSplFileInfo($file, $workdir);
        }

        return $paths;
    }
}
