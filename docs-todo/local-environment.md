# Local environment

## What it does

Runs a SightPress project on your own computer with Docker. One command starts the web server, PHP, an e-mail
catcher (Mailpit) and S3-compatible file storage (MinIO). A database server is optional: SightPress uses SQLite
unless you start MySQL, MariaDB or PostgreSQL with a profile.

Until a site is created, every address shows an accessible "No site configured" page (HTTP 404).

## Who uses it

Developers and operators trying SightPress or working on it. No SightPress permissions are involved.

## Commands

| Command                                     | What it does                                                  |
|---------------------------------------------|---------------------------------------------------------------|
| `docker compose up` / `make up`             | Start everything; the app is on http://localhost:8080          |
| `docker compose --profile mysql up`         | Also start MySQL (`mariadb` and `postgres` work the same way)  |
| `make up PROFILE=postgres`                  | The same with `make`                                           |
| `docker compose down` / `make down`         | Stop everything                                                |
| `composer up` / `composer down`             | The same through Composer                                      |
| `bin/sightpress list`                       | List the command line commands                                 |
| `bin/sightpress site:create demo`           | Create a site (not available yet: prints a message, exit 1)   |
| `make site-create NAME=demo`                | The same inside the PHP container                              |
| `make check`                                | Run `composer check` inside the PHP container                  |

Other addresses: Mailpit http://localhost:8025, MinIO console http://localhost:9001. All ports listen on
127.0.0.1 only.

## Settings

All in `.env` (copy `.env.example`; every value has a safe default): `APP_ENV`, `MAILER_DSN`, `S3_*`,
`MINIO_ROOT_USER`/`MINIO_ROOT_PASSWORD`, `DB_*` (database profiles), the `*_PORT` values and `UID`/`GID` (the user
the PHP container runs as). `config/tenant.php` is a stub that core will read later. The list of sites lives in
`config/sites.php`, which `bin/sightpress site:*` commands will write; no database is needed for it.

Database containers are only reachable from the other containers, by service name (`mysql`, `mariadb`,
`postgres`).

## Accessibility

The "No site configured" page has `lang="en-GB"`, a unique title, one `main` landmark, one `h1` and a plain
message. It follows the system light or dark colour scheme and has no other content. Any environment other than
`production` sends `X-Robots-Tag: noindex, nofollow`.

## Code and tests

- `public/index.php`, `src/Http/NoSiteConfiguredPage.php`, `tests/Unit/Http/NoSiteConfiguredPageTest.php`,
  `tests/Integration/FrontControllerTest.php`
- `bin/sightpress`, `src/Console/Application.php`, `tests/Unit/Console/ApplicationTest.php`,
  `tests/Integration/CommandLineTest.php`
- `docker-compose.yml`, `docker/php/Dockerfile`, `docker/nginx/default.conf`, `Makefile`, `.env.example`
