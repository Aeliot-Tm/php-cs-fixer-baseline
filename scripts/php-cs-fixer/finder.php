<?php

declare(strict_types=1);

return $finder = (new PhpCsFixer\Finder())
    ->files()
    ->in(dirname(__DIR__, 2))
    ->exclude(['tests/fixtures', 'vendor'])
    ->append([
        'bin/pcsf-baseline',
    ]);



