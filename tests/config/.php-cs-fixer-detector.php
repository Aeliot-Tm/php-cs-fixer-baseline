<?php

declare(strict_types=1);

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

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules($rules);
