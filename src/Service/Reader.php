<?php

namespace Aeliot\PhpCsFixerBaseline\Service;

use Aeliot\PhpCsFixerBaseline\Model\BaselineContent;
use Aeliot\PhpCsFixerBaseline\Model\BaselineFile;
use Aeliot\PhpCsFixerBaseline\Model\FileHash;

final class Reader
{
    public function read(string $path): BaselineFile
    {
        $json = file_exists($path)
            ? json_decode(file_get_contents($path), true, 512, \JSON_THROW_ON_ERROR)
            : [];

        $content = new BaselineContent();
        if ($configHash = $json['config_hash'] ?? null) {
            $content->setConfigHash($configHash);
        }

        foreach ($json['hashes'] as $filePath => $hash) {
            $content->addHash(new FileHash($filePath, $hash['hash']));
        }

        return new BaselineFile($path, $content);
    }
}
