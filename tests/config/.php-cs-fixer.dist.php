<?php

use Aeliot\PhpCsFixerBaseline\Service\FilterFactory;

$rules = [
    'header_comment' => [
        'header' => <<<'EOF'
            This file is part of the box project.

            (c) Anatoliy Melnikov <5785276@gmail.com>

            This source file is subject to the MIT license that is bundled
            with this source code in the file LICENSE.
            EOF,
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
