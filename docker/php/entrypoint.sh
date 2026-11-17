#!/bin/sh
# Installs the Composer dependencies on the first start, then runs the given command (php-fpm by default).
set -e

# Working on SightPress itself: docker-compose.siblings.yml mounts the core and module-interface checkouts at /core
# and /module-interface. Composer then takes them from there instead of Packagist.
for package in core module-interface; do
    if [ -f "/$package/composer.json" ]; then
        composer config --global "repositories.sightpress-$package" \
            "{\"type\": \"path\", \"url\": \"/$package\", \"options\": {\"symlink\": true}}"
    fi
done

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --no-progress
fi

exec docker-php-entrypoint "$@"
