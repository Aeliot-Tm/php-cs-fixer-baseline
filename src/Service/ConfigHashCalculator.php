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
