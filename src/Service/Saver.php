<?php

declare(strict_types=1);

namespace Aeliot\PhpCsFixerBaseline\Service;

use Aeliot\PhpCsFixerBaseline\Model\BaselineFile;

final class Saver
{
    public function save(BaselineFile $baseline): void
    {
        $content = json_encode($baseline->getContent(), \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
        file_put_contents($baseline->getPath(), $content);
    }
}
