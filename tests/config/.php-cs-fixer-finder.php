<?php

declare(strict_types=1);

return $finder = (new PhpCsFixer\Finder())
    ->files()
    ->name([
        'file-for-calculation-of-hash.php',
        'file-for-calculation-of-hash-second.php',
    ])
    ->in(__DIR__ . '/../fixtures/');



