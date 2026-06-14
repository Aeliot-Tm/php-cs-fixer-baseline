<?php
declare(strict_types=1);

namespace Aeliot\PhpCsFixerBaseline\Dto;

final readonly class FilterOptions
{
    private ?string $workdir;

    public function __construct(
        ?string $workdir = null
    )
    {
        $this->workdir = $workdir;
    }

    public function getWorkdir(): ?string
    {
        return $this->workdir;
    }
}
