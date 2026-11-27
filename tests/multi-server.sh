#!/usr/bin/env bash
# Checks that sign-ins work behind two web servers with no sticky sessions (H-9): starts docker-compose.multi.yml as
# its own compose project, creates a site, then sets up its first admin, signs in, opens the admin panel and signs out
# with every request going to the other PHP server. Then it removes everything it made.
#
#   tests/multi-server.sh database      sessions in the site's database (the default)
#   tests/multi-server.sh redis         sessions in Redis
#
# Needs docker compose and curl. Leaves config/sites.php as it was. MULTI_COMPOSE_EXTRA names one more compose file,
# for example docker-compose.siblings.yml to try checkouts of core next to this project.
set -euo pipefail

store=${1:-database}
case "$store" in database|redis) ;; *) echo "Usage: $0 database|redis" >&2; exit 2 ;; esac

cd "$(dirname "$0")/.."
port=${MULTI_HTTP_PORT:-8070}
base="http://localhost:$port"
project="sightpress-multi-$store"
work=$(mktemp -d)
export APP_SECRET=${APP_SECRET:-$(openssl rand -hex 32)}
export SESSION_STORE=$store
export HTTP_PORT=$port MAILPIT_UI_PORT=18025 MAILPIT_SMTP_PORT=11025 MINIO_API_PORT=19000 MINIO_CONSOLE_PORT=19001
compose=(docker compose -p "$project" -f docker-compose.yml -f docker-compose.multi.yml ${MULTI_COMPOSE_EXTRA:+-f "$MULTI_COMPOSE_EXTRA"} --profile redis)

cp config/sites.php "$work/sites.php"
cleanup() {
    "${compose[@]}" down --volumes --remove-orphans >/dev/null 2>&1 || true
    cp "$work/sites.php" config/sites.php
    rm -f config/sites.php.bak sites/multi-*.sqlite
    rm -rf "$work"
}
trap cleanup EXIT

fail() { echo "FAIL: $*" >&2; exit 1; }

# Install the dependencies once, before both servers start: two installs into the same folder would collide.
"${compose[@]}" build php
"${compose[@]}" run --rm --no-deps -T php true
"${compose[@]}" up --detach --wait php nginx redis

slug="multi-$store"
output=$("${compose[@]}" exec -T php bin/sightpress site:create "$slug" --no-interaction \
    --name="Two servers" --domain=localhost --url="$base" --owner-type=person --locale=en-GB --timezone=UTC \
    --db="sqlite:////app/sites/$slug.sqlite" \
    --admin-email=ada@example.org --admin-first-name=Ada --admin-last-name=Lovelace)
code=$(sed -n 's/.*Give the setup code \([0-9 ]*[0-9]\) to ada@example.org.*/\1/p' <<<"$output" | head -n1)
[ -n "$code" ] || fail "site:create printed no setup code: $output"

jar="$work/cookies"
# Requests a page and keeps its cookies; prints the body. A form's CSRF fields are kept in $work/csrf.
get() {
    curl -sS -b "$jar" -c "$jar" -H 'Host: localhost' "$base$1" -o "$work/page" -w '%{http_code} %{redirect_url}\n' >"$work/status"
    grep -o '<input type="hidden" name="csrf_[a-z]*" value="[^"]*">' "$work/page" \
        | sed 's/.*name="\([^"]*\)" value="\([^"]*\)".*/\1=\2/' >"$work/csrf" || true
}
post() {
    local path=$1; shift
    local fields=()
    while read -r pair; do fields+=(--data-urlencode "$(sed 's/&amp;/\&/g' <<<"$pair")"); done <"$work/csrf"
    for pair in "$@"; do fields+=(--data-urlencode "$pair"); done
    curl -sS -b "$jar" -c "$jar" -H 'Host: localhost' "$base$path" "${fields[@]}" -o "$work/page" \
        -w '%{http_code} %{redirect_url}\n' >"$work/status"
}
status() { cut -d' ' -f1 <"$work/status"; }
location() { cut -d' ' -f2 <"$work/status"; }

get /login/setup
post /login/setup email=ada@example.org "code=$code"
[ "$(status)" = 303 ] || fail "the setup code was refused: $(status) $(cat "$work/page" | head -c 400)"
get /login/password
post /login/password 'password=Correct horse battery staple 7!'
[ "$(status)" = 303 ] || fail "choosing the password failed: $(status)"
get /login
grep -q 'Your password has been set. Sign in with it now.' "$work/page" || fail "the flash message was lost"
post /login email=ada@example.org 'password=Correct horse battery staple 7!'
[ "$(status)" = 303 ] || fail "signing in failed: $(status)"
for _ in 1 2 3 4; do
    get /admin
    [ "$(status)" = 200 ] || fail "the admin panel didn't know the person on one server: $(status) $(location)"
done
get /logout
post /logout
[ "$(status)" = 303 ] || fail "signing out failed: $(status)"
get /admin
[ "$(status)" = 303 ] || fail "still signed in after signing out: $(status)"

servers=$("${compose[@]}" exec -T nginx cat /var/log/nginx/upstream.log | awk '{print $4}' | sort -u | grep -c . || true)
[ "$servers" -ge 2 ] || fail "only $servers PHP server answered; the check needs two"

# system:check may report other things (upload limits); only its sessions line matters here.
check=$("${compose[@]}" exec -T php bin/sightpress system:check 2>&1 || true)
if [ "$store" = database ]; then expected="Sessions: Sign-ins are kept in each site's database."; else expected="Sessions: Sign-ins are kept in Redis at redis:6379."; fi
grep -qF "$expected" <<<"$check" || fail "system:check doesn't say where sign-ins are kept: $check"
grep -q "could not reach the Redis server" <<<"$check" && fail "system:check can't reach Redis: $check"

echo "OK: sign-in, CSRF tokens and flash messages work across $servers PHP servers with session.store = $store."
