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

$configReader = Aeliot\PhpCsFixerBaseline\Service\ConfigReader::getInstance();

$finder = $configReader->getFinder();
$finder->filter((new FilterFactory())->createFilter($configReader->getBaselinePath(), $config));

return $config->setFinder($finder);
