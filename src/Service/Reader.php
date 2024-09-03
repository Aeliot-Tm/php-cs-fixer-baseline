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

        if (isset($json['relative'])) {
            $content->setRelative($json['relative']);
        }

        foreach ($json['hashes'] ?? [] as $filePath => $hash) {
            $content->addHash(new FileHash($filePath, $hash['hash']));
        }

        return new BaselineFile($path, $content);
    }
}
