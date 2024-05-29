<?php

use Aeliot\PhpCsFixerBaseline\Service\FilterFactory;

$rules = [
    '@Symfony' => true,
    '@Symfony:risky' => true,
    'concat_space' => [
        'spacing' => 'one',
    ],
    'phpdoc_align' => ['align' => 'left'],
];

$config = (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules($rules);

/** @var PhpCsFixer\Finder $finder */
$finder = require __DIR__ . '/.php-cs-fixer-finder.php';
$finder->filter((new FilterFactory())->createFilter(__DIR__ . '/.php-cs-fixer-baseline.json', $config));

return $config->setFinder($finder);
