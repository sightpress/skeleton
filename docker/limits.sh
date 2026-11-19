# shellcheck shell=sh
# Upload and input limits, from the operator's .env (see .env.example). Sourced at start by the PHP containers
# (docker/php/entrypoint.sh) and by nginx (as /docker-entrypoint.d/10-sightpress-limits.envsh), so PHP and nginx
# always use the same values. Sizes are whole megabytes or gigabytes: 64M, 1G.
#
#   UPLOAD_MAX_SIZE    largest file in one request          → upload_max_filesize
#   INPUT_MAX_SIZE     largest form without files            → post_max_size is the larger of this and the file + 1M
#   INPUT_MAX_VARS     fields in one form                    → max_input_vars
#   MAX_FILE_UPLOADS   files in one request                  → max_file_uploads
#   PHP_MEMORY_LIMIT   memory for one request                → memory_limit
#   UPLOAD_TIMEOUT     seconds a slow upload may take        → max_input_time, nginx client_body_timeout and
#                                                              fastcgi_read_timeout
#
# Exports SIGHTPRESS_BODY_LIMIT (post_max_size = nginx client_max_body_size) and every value above with its default.

sightpress_megabytes() {
    if ! printf '%s' "$2" | grep -Eq '^[0-9]+[MmGg]$'; then
        echo "SightPress: $1 must be a size such as 64M or 1G, not \"$2\"." >&2
        return 1
    fi
    case "$2" in
        *[Gg]) echo $(( ${2%?} * 1024 )) ;;
        *) echo $(( ${2%?} )) ;;
    esac
}

sightpress_number() {
    case "$2" in
        '' | *[!0-9]*)
            echo "SightPress: $1 must be a whole number, not \"$2\"." >&2
            return 1
            ;;
    esac
}

export UPLOAD_MAX_SIZE="${UPLOAD_MAX_SIZE:-64M}"
export INPUT_MAX_SIZE="${INPUT_MAX_SIZE:-8M}"
export INPUT_MAX_VARS="${INPUT_MAX_VARS:-5000}"
export MAX_FILE_UPLOADS="${MAX_FILE_UPLOADS:-20}"
export PHP_MEMORY_LIMIT="${PHP_MEMORY_LIMIT:-256M}"
export UPLOAD_TIMEOUT="${UPLOAD_TIMEOUT:-300}"

sightpress_upload_mb=$(sightpress_megabytes UPLOAD_MAX_SIZE "$UPLOAD_MAX_SIZE") || exit 1
sightpress_input_mb=$(sightpress_megabytes INPUT_MAX_SIZE "$INPUT_MAX_SIZE") || exit 1
sightpress_megabytes PHP_MEMORY_LIMIT "$PHP_MEMORY_LIMIT" > /dev/null || exit 1
sightpress_number INPUT_MAX_VARS "$INPUT_MAX_VARS" || exit 1
sightpress_number MAX_FILE_UPLOADS "$MAX_FILE_UPLOADS" || exit 1
sightpress_number UPLOAD_TIMEOUT "$UPLOAD_TIMEOUT" || exit 1

sightpress_body_mb=$(( sightpress_upload_mb + 1 ))
if [ "$sightpress_input_mb" -gt "$sightpress_body_mb" ]; then
    sightpress_body_mb=$sightpress_input_mb
fi
export SIGHTPRESS_BODY_LIMIT="${sightpress_body_mb}M"
unset sightpress_upload_mb sightpress_input_mb sightpress_body_mb
