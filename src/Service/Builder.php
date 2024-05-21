<?php

namespace Aeliot\PhpCsFixerBaseline\Service;

use Aeliot\PhpCsFixerBaseline\Model\BaselineContent;
use Aeliot\PhpCsFixerBaseline\Model\BaselineFile;
use Aeliot\PhpCsFixerBaseline\Model\FileHash;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

final class Builder
{
    private ConfigHashCalculator $configHashCalculator;
    private FileCacheCalculator $fileCacheCalculator;

    public function __construct()
    {
        $this->configHashCalculator = new ConfigHashCalculator();
        $this->fileCacheCalculator = new FileCacheCalculator();
    }

    public function create(string $path, Config $config, Finder $finder): BaselineFile
    {
        $content = new BaselineContent();
        $content->setConfigHash($this->configHashCalculator->calculate($config));

        foreach ($finder as $file) {
            $filePath = $file->getPathname();
            $content->addHash(new FileHash($filePath, $this->fileCacheCalculator->calculate($file)));
        }

        return new BaselineFile($path, $content);
    }
}
