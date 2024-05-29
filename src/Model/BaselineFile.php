<?php

declare(strict_types=1);

namespace Aeliot\PhpCsFixerBaseline\Model;

final class BaselineFile
{
    public function __construct(
        private string $path,
        private BaselineContent $content,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getContent(): BaselineContent
    {
        return $this->content;
    }

    public function getLockedFilesCount(): int
    {
        return $this->content->getHashesCount();
    }
}
