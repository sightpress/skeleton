<?php

declare(strict_types=1);

/*
 * Tenant configuration: where the site registry lives and where site files are stored.
 *
 * A stub until core reads it. Values come from the environment (see .env.example); the defaults keep everything
 * inside this project, with the registry in SQLite, so no database server is needed.
 */

$projectDir = dirname(__DIR__);
$registryUrl = getenv('SIGHTPRESS_REGISTRY_DATABASE_URL');

return [
    'registry' => [
        'database_url' => is_string($registryUrl) && $registryUrl !== ''
            ? $registryUrl
            : 'sqlite:///' . $projectDir . '/var/data/registry.sqlite',
    ],
    'sites_dir' => $projectDir . '/sites',
    'admin' => [
        'default_locale' => 'en-GB',
    ],
    'timezone' => 'UTC',
];
