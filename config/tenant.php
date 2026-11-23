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

// The web server's request body limit, which PHP can't read. In the docker compose setup nginx gets it from the same
// .env values as PHP (docker/limits.sh): the larger of UPLOAD_MAX_SIZE + 1M and INPUT_MAX_SIZE. Elsewhere, write
// your web server's limit instead, for example '65M' (nginx client_max_body_size, Apache LimitRequestBody).
$megabytes = static function (string $name, int $default): int {
    $value = getenv($name);
    if (!is_string($value) || preg_match('/^(\d+)([MmGg])$/', $value, $size) !== 1) {
        return $default;
    }

    return strtoupper($size[2]) === 'G' ? (int) $size[1] * 1024 : (int) $size[1];
};
$bodyLimit = max($megabytes('UPLOAD_MAX_SIZE', 64) + 1, $megabytes('INPUT_MAX_SIZE', 8)) . 'M';

return [
    'registry' => [
        'path' => $projectDir . '/config/sites.php',
    ],
    'sites_dir' => $projectDir . '/sites',
    'admin' => [
        'default_locale' => 'en-GB',
    ],
    'timezone' => 'UTC',
    // Optional modules ("features"). Each site's admins turn them on or off on the Features screen, or with
    // `bin/sightpress site:module:enable|disable`. `defaults` replaces a module's own default for new sites (existing
    // sites keep their choice); `locked` turns a module on (true) or off (false) for every site, and the sites' admins
    // see "Set by your organisation". Unknown module ids stop SightPress from starting. For example:
    // 'modules' => ['defaults' => ['analytics' => false], 'locked' => ['assistant' => false]],
    'modules' => [
        'defaults' => [],
        'locked' => [],
    ],
    // Themes installed with Composer (`composer require vendor/some-theme`, then `bin/sightpress cache:clear`).
    // `allowed` lists the theme ids sites may use (null = every installed theme); `default` is the theme of new sites
    // and must be allowed. `bin/sightpress theme:list` shows them and the sites that use each; a site's admins choose
    // its theme, or `bin/sightpress site:theme:apply <site> <theme>`. For example:
    // 'themes' => ['allowed' => ['default', 'acme-harbour'], 'default' => 'acme-harbour'],
    'themes' => [
        'allowed' => null,
        'default' => 'default',
    ],
    // Uploads: `bin/sightpress system:check` compares this with PHP's limits and the site's (see .env.example).
    'uploads' => [
        'proxy_body_limit' => $bodyLimit,
    ],
    // The largest file of each kind for every site, and under `max` the most a site's own limit can be
    // (`bin/sightpress site:media:limits <site> --audio=500M`). Without `max`, a site can only go lower. For example:
    // 'media' => ['limits' => ['image' => '20M', 'document' => '50M', 'max' => ['audio' => '500M', 'video' => '2G']]],
    'media' => [
        'limits' => [],
    ],
];
