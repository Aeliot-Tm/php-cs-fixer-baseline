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

return $finder = (new PhpCsFixer\Finder())
    ->files()
    ->in(dirname(__DIR__, 2))
    ->exclude(['tests/fixtures', 'vendor'])
    ->append([
        'bin/pcsf-baseline',
    ]);
