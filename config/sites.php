<?php

declare(strict_types=1);

/*
 * Site registry: which sites this install runs, their domains, status and (encrypted) database DSN.
 *
 * Written by `bin/sightpress site:*` commands; you can also edit it by hand. See ADR-0002.
 */

return [
    'version' => 1,
    'sites' => [],
];
