<?php

namespace Aeliot\PhpCsFixerBaseline\Service;

use PhpCsFixer\Config;

final class ConfigHashCalculator
{
    public function calculate(Config $config): int
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
}
