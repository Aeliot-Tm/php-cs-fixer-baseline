<?php

declare(strict_types=1);

$rules = [
    'array_syntax' => ['syntax' => 'short'],
];

return (new PhpCsFixer\Config())
    ->setRules($rules);
