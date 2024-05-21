<?php

declare(strict_types=1);

return $finder = (new PhpCsFixer\Finder())
    ->files()
    ->in(__DIR__)
    ->exclude(['var', 'vendor'])
    ->append([
        'bin/dev/php-cs-fixer-update-baseline',
    ]);



