<?php

use Aeliot\PhpCsFixerBaseline\Service\FilterFactory;

$rules = [
    '@Symfony' => true,
    '@Symfony:risky' => true,
    'concat_space' => [
        'spacing' => 'one',
    ],
    'header_comment' => [
        'header' => <<<'EOF'
            This file is part of the PHP CS Fixer Baseline project.

            (c) Anatoliy Melnikov <5785276@gmail.com>

            This source file is subject to the MIT license that is bundled
            with this source code in the file LICENSE.
            EOF,
    ],
    'phpdoc_align' => ['align' => 'left'],
];

$config = (new PhpCsFixer\Config())
    ->setCacheFile(dirname(__DIR__, 2) . '/var/php-cs-fixer/cache.json')
    ->setRiskyAllowed(true)
    ->setRules($rules);

/** @var PhpCsFixer\Finder $finder */
$finder = require __DIR__ . '/finder.php';
$finder->filter((new FilterFactory())->createFilter(__DIR__ . '/baseline.json', $config));

return $config->setFinder($finder);
