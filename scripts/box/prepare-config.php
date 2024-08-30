<?php

declare(strict_types=1);

$configPath = __DIR__ . '/config.json';
$config = json_decode(file_get_contents($configPath), true, 512, JSON_THROW_ON_ERROR);

$config['base-path'] = dirname($configPath, 2);

file_put_contents($config, json_encode($config, \JSON_THROW_ON_ERROR));
