#!/bin/sh
# Sets PHP's upload and input limits from .env, installs the Composer dependencies on the first start, then runs the
# given command (php-fpm by default).
set -e

# Upload and input limits (docker/limits.sh): written where PHP_INI_SCAN_DIR looks, so php-fpm and every command
# run in this container (`docker compose exec php bin/sightpress …`) use them.
sightpress-php-limits

# Working on SightPress itself: docker-compose.siblings.yml mounts the core, module-interface, theme-interface and
# theme-default checkouts at /core, /module-interface, /theme-interface and /theme-default. Composer then takes them
# from there instead of Packagist.
for package in core module-interface theme-interface theme-default; do
    if [ -f "/$package/composer.json" ]; then
        composer config --global "repositories.sightpress-$package" \
            "{\"type\": \"path\", \"url\": \"/$package\", \"options\": {\"symlink\": true}}"
    fi
done

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --no-progress
fi

exec docker-php-entrypoint "$@"
