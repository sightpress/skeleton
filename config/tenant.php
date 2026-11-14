<?php

declare(strict_types=1);

/*
 * Tenant configuration: where the site registry lives and where site files are stored.
 *
 * Core's kernel reads it on every start. The site registry is a config file (config/sites.php, see ADR-0002), so no
 * database server is needed. Only `bin/sightpress site:*` commands write it.
 *
 * Every key is optional. Others: `cache_dir` (default var/cache/<APP_ENV>), `log.path` (default
 * var/log/<APP_ENV>.log; a stream such as php://stderr also works) and `log.level` (default notice).
 */

$projectDir = dirname(__DIR__);

return [
    'registry' => [
        'path' => $projectDir . '/config/sites.php',
    ],
    'sites_dir' => $projectDir . '/sites',
    'admin' => [
        'default_locale' => 'en-GB',
    ],
    'timezone' => 'UTC',
];
