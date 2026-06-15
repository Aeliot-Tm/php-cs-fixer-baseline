<?php

declare(strict_types=1);

return $finder = (new PhpCsFixer\Finder())
    ->files()
    ->in(__DIR__ . '/../fixtures/')
    ->name([
        'file-for-calculation-of-hash.php',
        'file-for-calculation-of-hash-second.php',
        'file-compliant.php',
    ]);
