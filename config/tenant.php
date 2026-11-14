<?php

declare(strict_types=1);

/*
 * Tenant configuration: where the site registry lives and where site files are stored.
 *
 * A stub until core reads it. The site registry is a config file (config/sites.php, see ADR-0002), so no database
 * server is needed. Only `bin/sightpress site:*` commands write it.
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
