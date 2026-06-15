<?php

declare(strict_types=1);

/*
 * This file is part of the PHP CS Fixer Baseline project.
 *
 * (c) Anatoliy Melnikov <5785276@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Aeliot\PhpCsFixerBaseline\Dto;

final class FilterOptions
{
    public function __construct(
        private ?string $mode = null,
        private ?string $workdir = null,
    ) {
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function getWorkdir(): ?string
    {
        return $this->workdir;
    }
}
