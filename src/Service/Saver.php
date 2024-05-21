<?php

namespace Aeliot\PhpCsFixerBaseline\Service;

use Aeliot\PhpCsFixerBaseline\Model\BaselineFile;

final class Saver
{
    public function save(BaselineFile $baseline): void
    {
        file_put_contents($baseline->getPath(), json_encode($baseline, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT));
    }
}
