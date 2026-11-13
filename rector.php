<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/tools',
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/bin/sightpress',
    ])
    ->withRootFiles()
    ->withPhpSets(php85: true)
    ->withCache(__DIR__ . '/var/cache/rector');
