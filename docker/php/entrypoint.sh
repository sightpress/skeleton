#!/bin/sh
# Installs the Composer dependencies on the first start, then runs the given command (php-fpm by default).
set -e

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --no-progress
fi

exec docker-php-entrypoint "$@"
