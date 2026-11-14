# PHP-FPM image for a SightPress project.
#
#   base: PHP 8.5 FPM with the extensions SightPress needs (intl for dates and numbers in the visitor's language,
#         PDO for SQLite, MySQL/MariaDB and PostgreSQL).
#   dev:  base + PCOV for code coverage + Composer. docker-compose.yml uses this target.
FROM php:8.5-fpm AS base

RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev libpq-dev libsqlite3-dev \
    && docker-php-ext-install -j"$(nproc)" intl pdo_mysql pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

RUN { \
        echo 'date.timezone=UTC'; \
        echo 'expose_php=Off'; \
    } > /usr/local/etc/php/conf.d/sightpress.ini

WORKDIR /app

FROM base AS dev

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip $PHPIZE_DEPS \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/entrypoint.sh /usr/local/bin/sightpress-entrypoint

RUN { \
        echo 'memory_limit=512M'; \
        echo 'pcov.enabled=1'; \
    } > /usr/local/etc/php/conf.d/sightpress-dev.ini

ENV COMPOSER_HOME=/tmp/composer

ENTRYPOINT ["sightpress-entrypoint"]
CMD ["php-fpm"]
