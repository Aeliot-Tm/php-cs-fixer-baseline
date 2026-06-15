<?php

declare(strict_types=1);

return $finder = (new PhpCsFixer\Finder())
    ->files()
    ->in(__DIR__ . '/../fixtures/invalid-only/');
