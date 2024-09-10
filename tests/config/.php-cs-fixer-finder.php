<?php

declare(strict_types=1);

return $finder = (new PhpCsFixer\Finder())
    ->files()
    ->in('/app')
    ->exclude(['/app/test/fixtures', '/app/vendor'])
    ->append([
        'bin/pcsf-baseline',
    ]);



