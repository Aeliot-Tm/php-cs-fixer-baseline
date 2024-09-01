<?php

declare(strict_types=1);

/*
 * This file is part of the box project.
 *
 * (c) Anatoliy Melnikov <5785276@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Aeliot\PhpCsFixerBaseline\Model;

/*
 * This file is part of the box project.
 *
 * (c) Anatoliy Melnikov <5785276@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use PhpCsFixer\Config;
use PhpCsFixer\ConfigInterface;
use PhpCsFixer\Finder;

final class BuilderConfig
{
    /**
     * @var array{
     *     baselinePath: string,
     *     config: Config|ConfigInterface,
     *     finder: Finder,
     *     relative: bool,
     *     workdir: string|null
     * }
     */
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getBaselinePath(): string
    {
        return $this->config['baselinePath'];
    }

    public function getConfig(): Config|ConfigInterface
    {
        return $this->config['config'];
    }

    public function getFinder(): Finder
    {
        return $this->config['finder'];
    }

    public function isRelative(): bool
    {
        return $this->config['relative'] || $this->config['workdir'];
    }

    public function getWorkdir(): ?string
    {
        return $this->config['workdir'];
    }
}
