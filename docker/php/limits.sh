#!/bin/sh
# Writes PHP's upload and input limits from .env (docker/limits.sh) to $SIGHTPRESS_PHP_INI_DIR/limits.ini, which
# PHP reads after the image's own settings because PHP_INI_SCAN_DIR lists that folder last (docker-compose.yml).
set -e
. /usr/local/bin/sightpress-limits

directory="${SIGHTPRESS_PHP_INI_DIR:-/tmp/sightpress-php}"
mkdir -p "$directory"
cat > "$directory/limits.ini" <<INI
; Written by sightpress-php-limits from .env at container start. Change .env, not this file.
upload_max_filesize=$UPLOAD_MAX_SIZE
post_max_size=$SIGHTPRESS_BODY_LIMIT
max_input_vars=$INPUT_MAX_VARS
max_file_uploads=$MAX_FILE_UPLOADS
memory_limit=$PHP_MEMORY_LIMIT
max_input_time=$UPLOAD_TIMEOUT
INI
