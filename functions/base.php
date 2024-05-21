<?php

declare(strict_types=1);

function cs_fixer_get_baseline_file_path(): string
{
    return getcwd() . '/.php-cs-fixer-baseline.json';
}

function cs_fixer_get_path_hash(string $path): int
{
    return crc32(file_get_contents($path));
}

/**
 * @return array<string,mixed>
 */
function cs_fixer_get_baseline(): array
{
    $path = cs_fixer_get_baseline_file_path();

    return file_exists($path)
        ? json_decode(file_get_contents($path), true, 512, \JSON_THROW_ON_ERROR)
        : [];
}

/**
 * @return array<string,array{hash: int}>
 */
function cs_fixer_get_baseline_hashes(PhpCsFixer\Config $config): array
{
    $hashes = [];
    $baseline = cs_fixer_get_baseline();
    if ($baseline && cs_fixer_get_config_hash($config) === ($baseline['config_hash'] ?? null)) {
        $hashes = $baseline['hashes'] ?? [];
    }

    return $hashes;
}

function cs_fixer_get_config_hash(PhpCsFixer\Config $config): int
{
    $rules = $config->getRules();
    sort($rules);

    $data = [
        'risky_allowed' => $config->getRiskyAllowed(),
        'rules' => $rules,
    ];

    ksort($data);

    return crc32(json_encode($data, \JSON_THROW_ON_ERROR));
}

/**
 * @param array<string,array{hash: int}> $hashes
 */
function cs_fixer_put_baseline(array $hashes, PhpCsFixer\Config $config): void
{
    ksort($hashes);

    $baseline = [
        'config_hash' => cs_fixer_get_config_hash($config),
        'hashes' => $hashes,
    ];
    $path = cs_fixer_get_baseline_file_path();
    file_put_contents($path, json_encode($baseline, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT));
}
